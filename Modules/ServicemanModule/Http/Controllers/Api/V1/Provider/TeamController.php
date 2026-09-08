<?php

namespace Modules\ServicemanModule\Http\Controllers\Api\V1\Provider;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ServicemanModule\Http\Traits\SupervisorLeanApiTrait;
use Modules\UserManagement\Entities\Serviceman;

class TeamController extends Controller
{
    use SupervisorLeanApiTrait;

    private SupervisorTeam $team;
    private Serviceman $serviceman;
    private Booking $booking;

    public function __construct(SupervisorTeam $team, Serviceman $serviceman, Booking $booking)
    {
        $this->team = $team;
        $this->serviceman = $serviceman;
        $this->booking = $booking;
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'nullable|in:active,inactive,all']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $teams = $this->team->with([
                'servicemen.user',
                'bookings' => function ($query) {
                    $query->select('id', 'readable_id', 'booking_status', 'field_status', 'team_id', 'service_schedule')
                        ->whereNotIn('booking_status', ['canceled'])
                        ->latest();
                }])
            ->where('provider_id', $request->user()->provider->id)
            ->when(($request['status'] ?? 'all') !== 'all', function ($query) use ($request) {
                $query->where('is_active', $request['status'] === 'active');
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        if (supervisorMode()) {
            $teams = $this->mapLeanPaginator($teams, fn ($team) => $this->leanTeam($team));
        }

        return response()->json(response_formatter(DEFAULT_200, $teams), 200);
    }

    public function store(Request $request): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'serviceman_ids' => 'nullable|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;
        $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids'] ?? []);

        if (!empty($request['serviceman_ids']) && count($servicemanIds) !== count($request['serviceman_ids'])) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'invalid_serviceman', 'message' => translate('One or more servicemen do not belong to this supervisor')]]), 400);
        }

        $team = DB::transaction(function () use ($request, $providerId, $servicemanIds) {
            $team = $this->team->create([
                'provider_id' => $providerId,
                'name' => $request['name'],
                'is_active' => $request->boolean('is_active', true)]);

            if (!empty($servicemanIds)) {
                $team->servicemen()->sync($servicemanIds);
            }

            return $team->load('servicemen.user');
        });

        return response()->json(response_formatter(
            DEFAULT_STORE_200,
            supervisorMode() ? $this->leanTeam($team) : $team
        ), 200);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $team = $this->team->with([
                'servicemen.user',
                'bookings' => function ($query) {
                    $query->select('id', 'readable_id', 'booking_status', 'field_status', 'team_id', 'service_schedule')
                        ->whereNotIn('booking_status', ['canceled'])
                        ->latest();
                }])
            ->where(['id' => $id, 'provider_id' => $request->user()->provider->id])
            ->first();

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        return response()->json(response_formatter(
            DEFAULT_200,
            supervisorMode() ? $this->leanTeam($team) : $team
        ), 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:191',
            'serviceman_ids' => 'nullable|array|min:1',
            'serviceman_ids.*' => 'uuid',
            'is_active' => 'nullable|boolean']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $team = $this->team->where(['id' => $id, 'provider_id' => $request->user()->provider->id])->first();
        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $providerId = $request->user()->provider->id;
        $servicemanIds = null;
        if ($request->has('serviceman_ids')) {
            $servicemanIds = $this->resolveProviderServicemanIds($providerId, $request['serviceman_ids'] ?? []);
            if (count($servicemanIds) !== count($request['serviceman_ids'] ?? [])) {
                return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'invalid_serviceman', 'message' => translate('One or more servicemen do not belong to this supervisor')]]), 400);
            }
        }

        DB::transaction(function () use ($request, $team, $servicemanIds) {
            $team->fill($request->only(['name']));
            if ($request->has('is_active')) {
                $team->is_active = $request->boolean('is_active');
            }
            $team->save();

            if ($servicemanIds !== null) {
                $team->servicemen()->sync($servicemanIds);
            }
        });

        return response()->json(response_formatter(
            DEFAULT_UPDATE_200,
            supervisorMode() ? $this->leanTeam($team->fresh(['servicemen.user'])) : $team->fresh(['servicemen.user'])
        ), 200);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $team = $this->team->where(['id' => $id, 'provider_id' => $request->user()->provider->id])->first();
        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        if ($this->booking->where('team_id', $team->id)->exists()) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'team_in_use', 'message' => translate('Team is assigned to active bookings and cannot be deleted')]]), 400);
        }

        $team->servicemen()->detach();
        $team->delete();

        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

    public function assignToBooking(Request $request, string $bookingId): JsonResponse
    {
        if (supervisorMode()) {
            return jsonAdminAssignmentForbidden();
        }

        $validator = Validator::make($request->all(), [
            'team_id' => 'required|uuid']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;
        $booking = $this->booking->where(['id' => $bookingId, 'provider_id' => $providerId])->first();
        if (!$booking) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $team = $this->team->with('servicemen')->where(['id' => $request['team_id'], 'provider_id' => $providerId, 'is_active' => true])->first();
        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        if ($team->servicemen->isEmpty()) {
            return response()->json(response_formatter(DEFAULT_400, null, [['error_code' => 'empty_team', 'message' => translate('Team must have at least one serviceman')]]), 400);
        }

        $booking->team_id = $team->id;
        if (empty($booking->field_status)) {
            $booking->field_status = 'assigned';
            $booking->progress_percent = 0;
        }
        if ($booking->booking_status === 'pending') {
            $booking->booking_status = 'accepted';
        }
        $booking->supervisor_seen_at = null;
        $booking->supervisor_seen_by = null;
        $booking->save();

        $booking->load(['team.servicemen.user', 'attendances']);

        return response()->json(response_formatter(
            TEAM_ASSIGNED_TO_BOOKING_200,
            supervisorMode() ? $this->leanBookingDetail($booking) : $booking
        ), 200);
    }

    private function resolveProviderServicemanIds(string $providerId, array $servicemanIds): array
    {
        if (empty($servicemanIds)) {
            return [];
        }

        return $this->serviceman->where('provider_id', $providerId)
            ->whereIn('id', $servicemanIds)
            ->pluck('id')
            ->all();
    }
}
