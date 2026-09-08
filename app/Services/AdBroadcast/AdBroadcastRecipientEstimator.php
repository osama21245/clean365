<?php

namespace App\Services\AdBroadcast;

use Modules\UserManagement\Entities\User;

class AdBroadcastRecipientEstimator
{
    /**
     * @param  list<string>  $audiences
     * @param  list<string>|null  $zoneIds
     */
    public function estimate(array $audiences, ?array $zoneIds): int
    {
        $zoneIds = $zoneIds !== null && $zoneIds !== []
            ? array_values(array_unique(array_map('strval', $zoneIds)))
            : null;

        $total = 0;
        $tokenQuery = app(AdBroadcastTokenQuery::class);

        foreach ($audiences as $audience) {
            $total += match ($audience) {
                'customers', 'providers', 'servicemen' => $zoneIds === null
                    ? $tokenQuery->forAudienceAll($audience)->count()
                    : $tokenQuery->forAudience($audience, $zoneIds)->count(),
                'guests' => 0,
                default => 0,
            };
        }

        return $total;
    }

    /**
     * @param  list<string>|null  $zoneIds
     */
    protected function countForUserType(string $userType, ?array $zoneIds): int
    {
        $query = User::query()
            ->where('user_type', $userType)
            ->where('is_active', 1)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        return (int) $query->count();
    }
}
