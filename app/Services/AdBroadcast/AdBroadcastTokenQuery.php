<?php

namespace App\Services\AdBroadcast;

use Illuminate\Support\Collection;
use Modules\UserManagement\Entities\User;

class AdBroadcastTokenQuery
{
    /**
     * users.user_type mapping for dashboard audiences.
     */
    public static function userTypeForAudience(string $audienceKey): ?string
    {
        return match ($audienceKey) {
            'customers' => 'customer',
            'providers' => 'provider-admin',
            'servicemen' => 'provider-serviceman',
            default => null,
        };
    }

    /**
     * @param  list<string>  $zoneIds
     * @return Collection<int, array{user_id: string, token: string, ui_locale: string|null}>
     */
    public function forAudience(string $audienceKey, array $zoneIds): Collection
    {
        $type = self::userTypeForAudience($audienceKey);
        if ($type === null) {
            return collect();
        }

        $query = $this->baseTokenQuery($type);
        $this->applyZoneFilter($query, $audienceKey, $zoneIds);

        return $this->mapTokenRows($query->orderBy('id')->get());
    }

    /**
     * All active users in an audience (no zone filter) — for per-locale FCM multicast.
     *
     * @return Collection<int, array{user_id: string, token: string, ui_locale: string|null}>
     */
    public function forAudienceAll(string $audienceKey): Collection
    {
        $type = self::userTypeForAudience($audienceKey);
        if ($type === null) {
            return collect();
        }

        return $this->mapTokenRows(
            $this->baseTokenQuery($type)->orderBy('id')->get()
        );
    }

    /**
     * @param  list<string>  $userIds
     * @return Collection<int, array{user_id: string, token: string, ui_locale: string|null}>
     */
    public function forUserIds(array $userIds): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $userIds))));

        if ($ids === []) {
            return collect();
        }

        return $this->mapTokenRows(
            $this->baseTokenQuery()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->get()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function baseTokenQuery(?string $userType = null)
    {
        $query = User::query()
            ->select(['id', 'fcm_token'])
            ->where('is_active', 1)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        if ($userType !== null) {
            $query->where('user_type', $userType);
        }

        return $query;
    }

    /**
     * users has no zone_id column. Zone targeting for multicast is best-effort via
     * user_zones (customers) or Provider.zone_id (providers / servicemen). Topic jobs
     * remain the primary geo path (customer-{zone_id}, …).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     * @param  list<string>  $zoneIds
     */
    private function applyZoneFilter($query, string $audienceKey, array $zoneIds): void
    {
        $zoneIds = array_values(array_filter(array_map('strval', $zoneIds)));
        if ($zoneIds === []) {
            return;
        }

        // users.zone_id does not exist — skip a direct column filter.
        match ($audienceKey) {
            'customers' => $query->whereHas('zones', fn ($q) => $q->whereIn('zones.id', $zoneIds)),
            'providers' => $query->whereHas('provider', fn ($q) => $q->whereIn('zone_id', $zoneIds)),
            'servicemen' => $query->whereHas('serviceman.provider', fn ($q) => $q->whereIn('zone_id', $zoneIds)),
            default => null,
        };
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @return Collection<int, array{user_id: string, token: string, ui_locale: string|null}>
     */
    private function mapTokenRows($users): Collection
    {
        $fallbackLocale = (string) config('app.locale', 'en');

        return $users->map(fn (User $u): array => [
            'user_id' => (string) $u->id,
            'token' => (string) $u->fcm_token,
            'ui_locale' => $fallbackLocale,
        ]);
    }
}
