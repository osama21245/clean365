<?php

namespace Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ServicemanModule\Entities\SupervisorTeam;
use Modules\ServicemanModule\Http\Traits\SupervisorOpsTrait;

class TeamController extends Controller
{
    use SupervisorOpsTrait;

    private SupervisorTeam $team;

    public function __construct(SupervisorTeam $team)
    {
        $this->team = $team;
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'nullable|in:all,available,on_the_way,in_progress,unavailable',
            'search' => 'nullable|string|max:191']);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providerId = $request->user()->provider->id;
        $search = $request['search'] ?? null;

        $teams = $this->team->with(['servicemen.user'])
            ->where('provider_id', $providerId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('servicemen.user', function ($userQuery) use ($search) {
                            $userQuery->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->get()
            ->map(fn ($team) => $this->transformTeamCard($team));

        $summary = [
            'total' => $teams->count(),
            'available' => $teams->where('status', 'available')->count(),
            'in_progress' => $teams->where('status', 'in_progress')->count(),
            'on_the_way' => $teams->where('status', 'on_the_way')->count(),
            'unavailable' => $teams->where('status', 'unavailable')->count()];

        if (($request['status'] ?? 'all') !== 'all') {
            $teams = $teams->where('status', $request['status'])->values();
        } else {
            $teams = $teams->values();
        }

        $offset = max(0, ((int) $request['offset'] - 1) * (int) $request['limit']);
        $page = $teams->slice($offset, (int) $request['limit'])->values();

        return response()->json(response_formatter(DEFAULT_200, [
            'summary' => $summary,
            'teams' => [
                'data' => $page,
                'total' => $teams->count(),
                'limit' => (int) $request['limit'],
                'offset' => (int) $request['offset']]]), 200);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $team = $this->team->with(['servicemen.user'])
            ->where(['id' => $id, 'provider_id' => $request->user()->provider->id])
            ->first();

        if (!$team) {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

        $payload = $this->transformTeamCard($team);
        if ($payload['current_job']) {
            $job = $team->bookings()->where('id', $payload['current_job']['id'])->first();
            if ($job) {
                $payload['current_job'] = $this->transformJobDetail($job);
            }
        }

        return response()->json(response_formatter(DEFAULT_200, $payload), 200);
    }
}
