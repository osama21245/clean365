<?php

namespace Modules\ServicemanModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\UserManagement\Entities\Serviceman;

class TeamController extends Controller
{
    private SupervisorTeam $team;
    private Serviceman $serviceman;
    private Booking $booking;

    public function __construct(SupervisorTeam $team, Serviceman $serviceman, Booking $booking)
    {
        $this->team = $team;
        $this->serviceman = $serviceman;
        $this->booking = $booking;
    }

    public function index(Request $request): View
    {
        $request->validate(['status' => 'in:active,inactive,all']);

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $providerId = $request->user()->provider->id;

        $teams = $this->team->withCount('servicemen')
            ->with(['servicemen.user'])
            ->where('provider_id', $providerId)
            ->when($search, fn($query) => $query->where('name', 'LIKE', '%' . $search . '%'))
            ->when($status !== 'all', fn($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(pagination_limit())
            ->appends(['status' => $status, 'search' => $search]);

        return view('servicemanmodule::Provider.Team.list', compact('teams', 'search', 'status'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (supervisorMode()) {
            return webAdminAssignmentForbidden();
        }

        $servicemen = $this->getProviderServicemen($request->user()->provider->id);

        return view('servicemanmodule::Provider.Team.create', compact('servicemen'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (supervisorMode()) {
            return webAdminAssignmentForbidden();
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'serviceman_ids' => 'required|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        $providerId = $request->user()->provider->id;
        $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids']);

        if (count($servicemanIds) !== count($request['serviceman_ids'])) {
            Toastr::error(translate('One or more servicemen do not belong to this supervisor'));
            return back()->withInput();
        }

        DB::transaction(function () use ($request, $providerId, $servicemanIds) {
            $team = $this->team->create([
                'provider_id' => $providerId,
                'name' => $request['name'],
                'is_active' => $request->boolean('is_active', true)]);
            $team->servicemen()->sync($servicemanIds);
        });

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return redirect()->route('provider.team.list', ['status' => 'all']);
    }

    public function show(Request $request, string $id): View|RedirectResponse
    {
        $team = $this->team->with(['servicemen.user', 'bookings'])
            ->where(['id' => $id, 'provider_id' => $request->user()->provider->id])
            ->first();

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return redirect()->route('provider.team.list');
        }

        return view('servicemanmodule::Provider.Team.show', compact('team'));
    }

    public function edit(Request $request, string $id): View|RedirectResponse
    {
        if (supervisorMode()) {
            return webAdminAssignmentForbidden();
        }

        $providerId = $request->user()->provider->id;
        $team = $this->team->with('servicemen')->where(['id' => $id, 'provider_id' => $providerId])->first();

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return redirect()->route('provider.team.list');
        }

        $servicemen = $this->getProviderServicemen($providerId);
        $selectedServicemanIds = $team->servicemen->pluck('id')->all();

        return view('servicemanmodule::Provider.Team.edit', compact('team', 'servicemen', 'selectedServicemanIds'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        if (supervisorMode()) {
            return webAdminAssignmentForbidden();
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'serviceman_ids' => 'required|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        $providerId = $request->user()->provider->id;
        $team = $this->team->where(['id' => $id, 'provider_id' => $providerId])->first();

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return back();
        }

        $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids']);
        if (count($servicemanIds) !== count($request['serviceman_ids'])) {
            Toastr::error(translate('One or more servicemen do not belong to this supervisor'));
            return back()->withInput();
        }

        DB::transaction(function () use ($request, $team, $servicemanIds) {
            $team->update([
                'name' => $request['name'],
                'is_active' => $request->boolean('is_active', true)]);
            $team->servicemen()->sync($servicemanIds);
        });

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return redirect()->route('provider.team.show', $team->id);
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        if (supervisorMode()) {
            return webAdminAssignmentForbidden();
        }

        $team = $this->team->where(['id' => $id, 'provider_id' => $request->user()->provider->id])->first();

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return back();
        }

        if ($this->booking->where('team_id', $team->id)->exists()) {
            Toastr::error(translate('Team is assigned to active bookings and cannot be deleted'));
            return back();
        }

        $team->servicemen()->detach();
        $team->delete();

        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return redirect()->route('provider.team.list', ['status' => 'all']);
    }

    public function statusUpdate(Request $request, string $id): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $team = $this->team->where(['id' => $id, 'provider_id' => $request->user()->provider->id])->first();

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $team->is_active = !$team->is_active;
        $team->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function assignToBooking(Request $request, string $bookingId): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $request->validate(['team_id' => 'required|uuid']);

        $providerId = $request->user()->provider->id;
        $booking = $this->booking->where(['id' => $bookingId, 'provider_id' => $providerId])->first();

        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $team = $this->team->with('servicemen.user')
            ->where(['id' => $request['team_id'], 'provider_id' => $providerId, 'is_active' => true])
            ->first();

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        if ($team->servicemen->isEmpty()) {
            return response()->json(response_formatter(DEFAULT_400, null, [['message' => translate('Team must have at least one serviceman')]]), 400);
        }

        $booking->team_id = $team->id;
        $booking->supervisor_seen_at = null;
        $booking->supervisor_seen_by = null;
        $booking->save();

        return response()->json(response_formatter(TEAM_ASSIGNED_TO_BOOKING_200, [
            'team' => $team,
            'booking_id' => $booking->id]), 200);
    }

    private function getProviderServicemen(string $providerId)
    {
        return $this->serviceman->with('user')
            ->where('provider_id', $providerId)
            ->whereHas('user', fn($query) => $query->ofStatus(1))
            ->latest()
            ->get();
    }

    private function resolveProviderServicemanIds(string $providerId, array $servicemanIds): array
    {
        return $this->serviceman->where('provider_id', $providerId)
            ->whereIn('id', $servicemanIds)
            ->pluck('id')
            ->all();
    }
}
