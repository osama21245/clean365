<?php

namespace Modules\ServicemanModule\Services\BookingAutoAssign;

use Modules\BookingModule\Entities\Booking;

/**
 * One-line entry point for create flows.
 * Keep create controllers thin; all logic stays in BookingAutoAssignService.
 */
final class BookingAutoAssign
{
    public static function handle(Booking $booking): array
    {
        return app(BookingAutoAssignService::class)->assign($booking);
    }
}
