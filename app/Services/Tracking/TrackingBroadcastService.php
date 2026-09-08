<?php

namespace App\Services\Tracking;

use App\Events\Tracking\BookingAgentLocationUpdated;
use App\Events\Tracking\BookingTrackingEnded;
use App\Events\Tracking\BookingTrackingStarted;
use App\Support\Tracking\BroadcastClientConfig;
use Illuminate\Support\Facades\Cache;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

class TrackingBroadcastService
{
    public function enabled(): bool
    {
        return BroadcastClientConfig::enabled();
    }

    /**
     * @return list<string>
     */
    public function trackingFieldStatuses(): array
    {
        return config(
            'tracking.customer_agent_location.visible_field_statuses',
            config('tracking.busy_field_statuses', ['on_the_way', 'arrived', 'in_progress'])
        );
    }

    public function isTrackingFieldStatus(?string $fieldStatus): bool
    {
        return in_array((string) $fieldStatus, $this->trackingFieldStatuses(), true);
    }

    public function broadcastTrackingStarted(Booking $booking): void
    {
        if (!$this->enabled() || !$this->isTrackingFieldStatus($booking->field_status)) {
            return;
        }

        $booking->loadMissing(['provider.owner', 'serviceman.user', 'service_address']);

        event(new BookingTrackingStarted($booking->id, [
            'booking_id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'field_status' => $booking->field_status,
            'booking_status' => $booking->booking_status,
            'progress_percent' => (int) ($booking->progress_percent ?? 0),
            'agent' => $this->agentSnapshotFromBooking($booking),
            'destination' => $this->destinationCoords($booking),
        ]));
    }

    public function broadcastTrackingEnded(Booking $booking, string $reason = 'completed'): void
    {
        if (!$this->enabled()) {
            return;
        }

        event(new BookingTrackingEnded($booking->id, [
            'booking_id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'field_status' => $booking->field_status,
            'booking_status' => $booking->booking_status,
            'reason' => $reason,
        ]));
    }

    /**
     * Broadcast live GPS for all active tracking bookings linked to this agent.
     */
    public function broadcastAgentLocation(User $user, ?float $previousLat = null, ?float $previousLng = null): void
    {
        if (!$this->enabled()) {
            return;
        }

        $lat = $this->toFloatOrNull($user->latitude);
        $lng = $this->toFloatOrNull($user->longitude);
        if ($lat === null || $lng === null) {
            return;
        }

        if (!$this->shouldBroadcastMove($user->id, $lat, $lng, $previousLat, $previousLng)) {
            return;
        }

        $bookingIds = $this->activeTrackingBookingIdsForAgent($user);
        if ($bookingIds === []) {
            // Still push to admin live-map as a single-channel event with empty booking list.
            event(new BookingAgentLocationUpdated([], [
                'booking_ids' => [],
                'agent' => $this->agentSnapshotFromUser($user, $lat, $lng),
            ]));

            return;
        }

        event(new BookingAgentLocationUpdated($bookingIds, [
            'booking_ids' => $bookingIds,
            'agent' => $this->agentSnapshotFromUser($user, $lat, $lng),
        ]));
    }

    /**
     * @return list<string>
     */
    public function activeTrackingBookingIdsForAgent(User $user): array
    {
        $statuses = $this->trackingFieldStatuses();
        $query = Booking::query()
            ->whereNotIn('booking_status', ['completed', 'canceled'])
            ->whereIn('field_status', $statuses);

        if ($user->user_type === 'provider-admin') {
            $providerId = Provider::query()->where('user_id', $user->id)->value('id');
            if (!$providerId) {
                return [];
            }
            $query->where('provider_id', $providerId);
        } elseif ($user->user_type === 'provider-serviceman') {
            $servicemanId = Serviceman::query()->where('user_id', $user->id)->value('id');
            if (!$servicemanId) {
                return [];
            }
            $query->where(function ($q) use ($servicemanId) {
                $q->where('serviceman_id', $servicemanId)
                    ->orWhereIn('team_id', function ($sub) use ($servicemanId) {
                        $sub->select('team_id')
                            ->from('supervisor_team_servicemen')
                            ->where('serviceman_id', $servicemanId);
                    });
            });
        } else {
            return [];
        }

        return $query->pluck('id')->map(fn ($id) => (string) $id)->unique()->values()->all();
    }

    private function shouldBroadcastMove(
        string $userId,
        float $lat,
        float $lng,
        ?float $previousLat,
        ?float $previousLng
    ): bool {
        $throttleSeconds = (float) config('tracking.broadcast.throttle_seconds', 3);
        $minMeters = (float) config('tracking.broadcast.min_move_meters', 15);
        $cacheKey = 'tracking:broadcast:last:'.$userId;

        $last = Cache::get($cacheKey);
        $now = microtime(true);

        if (is_array($last)) {
            $elapsed = $now - (float) ($last['at'] ?? 0);
            $lastLat = isset($last['lat']) ? (float) $last['lat'] : null;
            $lastLng = isset($last['lng']) ? (float) $last['lng'] : null;

            if ($elapsed < $throttleSeconds && $lastLat !== null && $lastLng !== null) {
                $moved = $this->haversineMeters($lastLat, $lastLng, $lat, $lng);
                if ($moved < $minMeters) {
                    return false;
                }
            }
        } elseif ($previousLat !== null && $previousLng !== null) {
            $moved = $this->haversineMeters($previousLat, $previousLng, $lat, $lng);
            if ($moved < $minMeters && $throttleSeconds > 0) {
                // First cache miss but tiny move — still allow first broadcast after login.
            }
        }

        Cache::put($cacheKey, ['at' => $now, 'lat' => $lat, 'lng' => $lng], 120);

        return true;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function agentSnapshotFromBooking(Booking $booking): ?array
    {
        $owner = $booking->provider?->owner;
        if ($owner instanceof User) {
            return $this->agentSnapshotFromUser(
                $owner,
                $this->toFloatOrNull($owner->latitude),
                $this->toFloatOrNull($owner->longitude)
            );
        }

        $servicemanUser = $booking->serviceman?->user;
        if ($servicemanUser instanceof User) {
            return $this->agentSnapshotFromUser(
                $servicemanUser,
                $this->toFloatOrNull($servicemanUser->latitude),
                $this->toFloatOrNull($servicemanUser->longitude)
            );
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function agentSnapshotFromUser(User $user, ?float $lat, ?float $lng): array
    {
        $role = $user->user_type === 'provider-serviceman' ? 'serviceman' : 'supervisor';

        return [
            'id' => $user->id,
            'role' => $role,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: null,
            'phone' => $user->phone,
            'latitude' => $lat,
            'longitude' => $lng,
            'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            'is_online' => $user->isOnline(),
        ];
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function destinationCoords(Booking $booking): ?array
    {
        if ($booking->service_address_location) {
            $location = is_string($booking->service_address_location)
                ? json_decode($booking->service_address_location, true)
                : (array) $booking->service_address_location;

            $latitude = $location['lat'] ?? $location['latitude'] ?? null;
            $longitude = $location['lon'] ?? $location['longitude'] ?? $location['lng'] ?? null;

            if ($latitude !== null && $longitude !== null && $latitude !== '' && $longitude !== '') {
                return ['lat' => (float) $latitude, 'lng' => (float) $longitude];
            }
        }

        $address = $booking->service_address;
        if ($address && $address->lat !== null && $address->lon !== null
            && $address->lat !== '' && $address->lon !== '') {
            return [
                'lat' => (float) $address->lat,
                'lng' => (float) $address->lon,
            ];
        }

        return null;
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
