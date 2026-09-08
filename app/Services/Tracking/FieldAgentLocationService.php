<?php

namespace App\Services\Tracking;

use Modules\UserManagement\Entities\User;

class FieldAgentLocationService
{
    public function __construct(
        private readonly TrackingBroadcastService $broadcastService
    ) {}

    /**
     * Persist live GPS + heartbeat for a supervisor or serviceman.
     */
    public function update(User $user, ?float $latitude, ?float $longitude): User
    {
        $previousLat = $user->latitude !== null && $user->latitude !== ''
            ? (float) $user->latitude
            : null;
        $previousLng = $user->longitude !== null && $user->longitude !== ''
            ? (float) $user->longitude
            : null;

        $moved = false;

        if ($latitude !== null) {
            $user->latitude = (string) $latitude;
            $moved = true;
        }

        if ($longitude !== null) {
            $user->longitude = (string) $longitude;
            $moved = true;
        }

        $user->last_seen_at = now();
        $user->save();

        $fresh = $user->fresh();

        if ($moved) {
            $this->broadcastService->broadcastAgentLocation($fresh, $previousLat, $previousLng);
        }

        return $fresh;
    }
}
