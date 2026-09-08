<?php

namespace App\Services\Tracking;

use Modules\BookingModule\Entities\Booking;
use Modules\UserManagement\Entities\User;

/**
 * Resolve live agent GPS for customer booking tracking.
 */
class CustomerBookingAgentLocationService
{
    /**
     * Build the customer-facing agent location payload for a booking.
     *
     * Location is exposed only when:
     * - feature enabled
     * - booking is not completed/canceled
     * - field_status is in the visible list (default: on_the_way, arrived, in_progress)
     * - the primary agent has live lat/lng on users
     *
     * @return array{
     *   available: bool,
     *   reason: ?string,
     *   tracking_active: bool,
     *   booking_id: string,
     *   readable_id: mixed,
     *   field_status: ?string,
     *   booking_status: ?string,
     *   progress_percent: int,
     *   agent: ?array,
     *   destination: ?array{lat: float, lng: float}
     * }
     */
    public function forBooking(Booking $booking): array
    {
        $booking->loadMissing([
            'provider.owner',
            'serviceman.user',
            'service_address',
        ]);

        $visibleStatuses = config(
            'tracking.customer_agent_location.visible_field_statuses',
            config('tracking.busy_field_statuses', ['on_the_way', 'arrived', 'in_progress'])
        );

        $base = [
            'available' => false,
            'reason' => null,
            'tracking_active' => false,
            'booking_id' => $booking->id,
            'readable_id' => $booking->readable_id,
            'field_status' => $booking->field_status,
            'booking_status' => $booking->booking_status,
            'progress_percent' => (int) ($booking->progress_percent ?? 0),
            'agent' => null,
            'destination' => $this->destinationCoords($booking),
        ];

        if (!(bool) config('tracking.customer_agent_location.enabled', true)) {
            $base['reason'] = 'disabled';
            return $base;
        }

        if (in_array($booking->booking_status, ['completed', 'canceled'], true)) {
            $base['reason'] = 'booking_closed';
            return $base;
        }

        $fieldStatus = (string) ($booking->field_status ?? '');
        $trackingActive = in_array($fieldStatus, $visibleStatuses, true);
        $base['tracking_active'] = $trackingActive;

        if (!$trackingActive) {
            $base['reason'] = 'not_en_route';
            return $base;
        }

        $agentUser = $this->resolvePrimaryAgentUser($booking);
        if (!$agentUser) {
            $base['reason'] = 'no_agent';
            return $base;
        }

        $lat = $this->toFloatOrNull($agentUser->latitude);
        $lng = $this->toFloatOrNull($agentUser->longitude);

        if ($lat === null || $lng === null) {
            $base['reason'] = 'no_gps';
            $base['agent'] = $this->agentMeta($agentUser, $booking, null, null);
            return $base;
        }

        $base['available'] = true;
        $base['reason'] = null;
        $base['agent'] = $this->agentMeta($agentUser, $booking, $lat, $lng);

        return $base;
    }

    private function resolvePrimaryAgentUser(Booking $booking): ?User
    {
        // Team / supervisor jobs: prefer the supervisor (provider owner).
        $owner = $booking->provider?->owner;
        if ($owner instanceof User) {
            return $owner;
        }

        // Solo serviceman assignment.
        $servicemanUser = $booking->serviceman?->user;
        if ($servicemanUser instanceof User) {
            return $servicemanUser;
        }

        return null;
    }

    private function agentMeta(User $user, Booking $booking, ?float $lat, ?float $lng): array
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
            'provider_id' => $booking->provider_id,
            'serviceman_id' => $booking->serviceman_id,
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
