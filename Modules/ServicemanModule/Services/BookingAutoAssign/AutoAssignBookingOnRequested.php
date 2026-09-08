<?php

namespace Modules\ServicemanModule\Services\BookingAutoAssign;

use Modules\BookingModule\Events\BookingRequested;

/**
 * Thin listener — all logic lives in BookingAutoAssignService.
 */
class AutoAssignBookingOnRequested
{
    public function __construct(
        private readonly BookingAutoAssignService $autoAssign
    ) {
    }

    public function handle(BookingRequested $event): void
    {
        if (empty($event->booking?->id)) {
            return;
        }

        $this->autoAssign->assign($event->booking);
    }
}
