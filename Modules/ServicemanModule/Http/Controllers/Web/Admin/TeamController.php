<?php

namespace Modules\ServicemanModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\UserManagement\Entities\Serviceman;

class TeamController extends Controller
{
    private SupervisorTeam $team;
    private Serviceman $serviceman;
    private Booking $booking;
    private Provider $provider;

    public function __construct(
        SupervisorTeam $team,
        Serviceman $serviceman,
        Booking $booking,
        Provider $provider
    ) {
        $this->team = $team;
        $this->serviceman = $serviceman;
        $this->booking = $booking;
        $this->provider = $provider;
    }

    public function index(Request $request): View
    {
        $request->validate([
            'status' => 'in:active,inactive,all',
            'provider_id' => 'nullable|uuid']);

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $providerId = $request->get('provider_id');

        $teams = $this->team->withCount('servicemen')
            ->with(['servicemen.user', 'provider'])
            ->when($providerId, fn($query) => $query->where('provider_id', $providerId))
            ->when($search, fn($query) => $query->where('name', 'LIKE', '%' . $search . '%'))
            ->when($status !== 'all', fn($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(pagination_limit())
            ->appends(['status' => $status, 'search' => $search, 'provider_id' => $providerId]);

        $providers = $this->getActiveProviders();

        return view('servicemanmodule::Admin.Team.list', compact('teams', 'search', 'status', 'providers', 'providerId'));
    }

    public function create(Request $request): View
    {
        $providers = $this->getActiveProviders();
        $providerId = $request->get('provider_id', old('provider_id'));
        $servicemen = $providerId ? $this->getProviderServicemen($providerId) : collect();

        return view('servicemanmodule::Admin.Team.create', compact('providers', 'servicemen', 'providerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'provider_id' => 'required|uuid|exists:providers,id',
            'name' => 'required|string|max:191',
            'serviceman_ids' => 'required|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        $providerId = $request['provider_id'];
        $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids']);

        if (count($servicemanIds) !== count($request['serviceman_ids'])) {
            Toastr::error(translate('One or more servicemen are not available for this team'));
            return back()->withInput();
        }

        DB::transaction(function () use ($request, $providerId, $servicemanIds) {
            $team = $this->team->create([
                'provider_id' => $providerId,
                'name' => $request['name'],
                'is_active' => $request->boolean('is_active', true)]);
            $this->syncTeamServicemen($team, $providerId, $servicemanIds);
        });

        Toastr::success(translate(DEFAULT_STORE_200['key']));
        return redirect()->route('admin.team.list', ['status' => 'all']);
    }

    public function show(string $id): View|RedirectResponse
    {
        $team = $this->team->with(['servicemen.user', 'bookings', 'provider'])->find($id);

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return redirect()->route('admin.team.list');
        }

        return view('servicemanmodule::Admin.Team.show', compact('team'));
    }

    public function edit(string $id): View|RedirectResponse
    {
        $team = $this->team->with('servicemen')->find($id);

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return redirect()->route('admin.team.list');
        }

        $providers = $this->getActiveProviders();
        $servicemen = $this->getProviderServicemen($team->provider_id);
        $selectedServicemanIds = $team->servicemen->pluck('id')->all();

        return view('servicemanmodule::Admin.Team.edit', compact('team', 'providers', 'servicemen', 'selectedServicemanIds'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'provider_id' => 'required|uuid|exists:providers,id',
            'name' => 'required|string|max:191',
            'serviceman_ids' => 'required|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        $team = $this->team->find($id);

        if (!$team) {
            Toastr::error(translate(DEFAULT_404['key']));
            return back();
        }

        $providerId = $request['provider_id'];
        $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids']);
        if (count($servicemanIds) !== count($request['serviceman_ids'])) {
            Toastr::error(translate('One or more servicemen are not available for this team'));
            return back()->withInput();
        }

        DB::transaction(function () use ($request, $team, $providerId, $servicemanIds) {
            $team->update([
                'provider_id' => $providerId,
                'name' => $request['name'],
                'is_active' => $request->boolean('is_active', true)]);
            $this->syncTeamServicemen($team, $providerId, $servicemanIds);
        });

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return redirect()->route('admin.team.show', $team->id);
    }

    public function destroy(string $id): RedirectResponse
    {
        $team = $this->team->find($id);

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
        return redirect()->route('admin.team.list', ['status' => 'all']);
    }

    public function statusUpdate(string $id): JsonResponse
    {
        $team = $this->team->find($id);

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $team->is_active = !$team->is_active;
        $team->save();

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    public function assignToBooking(Request $request, string $bookingId): JsonResponse
    {
        $request->validate(['team_id' => 'required|uuid']);

        $booking = $this->booking->find($bookingId);

        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $team = $this->team->with('servicemen.user')
            ->where(['id' => $request['team_id'], 'provider_id' => $booking->provider_id, 'is_active' => true])
            ->first();

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        if ($team->servicemen->isEmpty()) {
            return response()->json(response_formatter(DEFAULT_400, null, [['message' => translate('Team must have at least one serviceman')]]), 400);
        }

        if (!bookingCanReassignCrew($booking)) {
            return response()->json(response_formatter(DEFAULT_400, null, [[
                'message' => translate('Cannot change team after the booking is on the way')
            ]]), 400);
        }

        $booking->team_id = $team->id;
        if (supervisorMode()) {
            if (empty($booking->field_status)) {
                $booking->field_status = 'assigned';
                $booking->progress_percent = 0;
            }
            if ($booking->booking_status === 'pending') {
                $booking->booking_status = 'accepted';
            }
        }
        $booking->supervisor_seen_at = null;
        $booking->supervisor_seen_by = null;
        $booking->save();

        return response()->json(response_formatter(TEAM_ASSIGNED_TO_BOOKING_200, [
            'team' => $team,
            'booking_id' => $booking->id]), 200);
    }

    public function servicemenByProvider(Request $request): JsonResponse
    {
        $request->validate(['provider_id' => 'required|uuid']);

        $servicemen = $this->getProviderServicemen($request['provider_id'])->map(function ($serviceman) {
            return [
                'id' => $serviceman->id,
                'name' => trim(($serviceman->user?->first_name ?? '') . ' ' . ($serviceman->user?->last_name ?? '')),
                'phone' => $serviceman->user?->phone];
        });

        return response()->json(response_formatter(DEFAULT_200, $servicemen), 200);
    }

    private function getActiveProviders()
    {
        return $this->provider->ofApproval(1)->ofStatus(1)->orderBy('company_name')->get(['id', 'company_name']);
    }

    private function getProviderServicemen(string $providerId)
    {
        return $this->serviceman->with('user')
            ->where(function ($query) use ($providerId) {
                $query->whereNull('provider_id')
                    ->orWhere('provider_id', $providerId);
            })
            ->whereHas('user', fn($query) => $query->ofStatus(1))
            ->latest()
            ->get();
    }

    private function resolveProviderServicemanIds(string $providerId, array $servicemanIds): array
    {
        return $this->serviceman->whereIn('id', $servicemanIds)
            ->where(function ($query) use ($providerId) {
                $query->whereNull('provider_id')
                    ->orWhere('provider_id', $providerId);
            })
            ->pluck('id')
            ->all();
    }

    private function syncTeamServicemen(SupervisorTeam $team, string $providerId, array $servicemanIds): void
    {
        $team->servicemen()->sync($servicemanIds);
        $this->serviceman->whereIn('id', $servicemanIds)->update(['provider_id' => $providerId]);
    }
}
