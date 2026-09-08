<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Tracking\FieldAgentLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\UserManagement\Entities\User;

class FieldAgentLocationController extends Controller
{
    public function __construct(
        private readonly FieldAgentLocationService $locationService
    ) {}

    /**
     * Heartbeat + optional GPS write for supervisors and servicemen.
     * PUT body: { latitude?, longitude? }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!in_array($user->user_type, ['provider-admin', 'provider-serviceman'], true)) {
            return response()->json([
                'response_code' => 'unauthorized_403',
                'message' => translate('You are not allowed to update field location'),
            ], 403);
        }

        $latitude = $request->has('latitude') ? (float) $request->input('latitude') : null;
        $longitude = $request->has('longitude') ? (float) $request->input('longitude') : null;

        $user = $this->locationService->update($user, $latitude, $longitude);

        return response()->json([
            'response_code' => 'default_update_200',
            'message' => translate('successfully updated'),
            'content' => [
                'latitude' => $user->latitude !== null ? (float) $user->latitude : null,
                'longitude' => $user->longitude !== null ? (float) $user->longitude : null,
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            ],
        ]);
    }
}
