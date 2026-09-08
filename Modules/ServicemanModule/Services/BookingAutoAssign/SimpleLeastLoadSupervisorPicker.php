<?php

namespace Modules\ServicemanModule\Services\BookingAutoAssign;

use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;

/**
 * v1 strategy: active + approved supervisor with the fewest open bookings.
 * Prefer same zone when configured; otherwise any zone.
 */
class SimpleLeastLoadSupervisorPicker implements SupervisorPickerInterface
{
    public function pick(Booking $booking): ?Provider
    {
        $openStatuses = config('booking_auto_assign.open_booking_statuses', ['pending', 'accepted', 'ongoing']);
        $preferSameZone = (bool) config('booking_auto_assign.prefer_same_zone', true);

        $base = Provider::query()
            ->ofStatus(1)
            ->ofApproval(1)
            ->withCount([
                'bookings as open_bookings_count' => function ($query) use ($openStatuses) {
                    $query->whereIn('booking_status', $openStatuses);
                },
            ])
            ->orderBy('open_bookings_count')
            ->orderBy('created_at');

        if ($preferSameZone && !empty($booking->zone_id)) {
            $sameZone = (clone $base)->where('zone_id', $booking->zone_id)->first();
            if ($sameZone) {
                return $sameZone;
            }
        }

        return $base->first();
    }
}
