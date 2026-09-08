<?php

namespace Modules\ServicemanModule\Services\BookingAutoAssign;

use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;

/**
 * Swap implementations via config('booking_auto_assign.picker').
 */
interface SupervisorPickerInterface
{
    /**
     * Pick a free (or least-loaded) supervisor for this booking.
     */
    public function pick(Booking $booking): ?Provider;
}
