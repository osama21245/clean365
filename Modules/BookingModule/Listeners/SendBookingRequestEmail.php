<?php

namespace Modules\BookingModule\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\BookingModule\Emails\BookingMail;
use Modules\BookingModule\Events\BookingRequested;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingRequestEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param BookingRequested $event
     * @return void
     */
    public function handle(BookingRequested $event)
    {
        try {
            $email = isNotificationActive(null, 'booking', 'email', 'user');
            $emailServices =  business_config('email_config_status', 'email_config');

            if (isset($event->booking->customer->email) && isset($emailServices) && $emailServices->live_values == 1 && $email) {
                Mail::to($event->booking->customer->email)->send(new BookingMail($event->booking));
            }
        } catch (\Exception $exception) {
            info($exception);
        }

        // If auto-assign already moved the booking to accepted, the Booking model
        // observer already pushed "booking_accepted". Do not also send "booking_place"
        // (that was causing 2 different customer pushes on subscribe/confirm).
        if (($event->booking->booking_status ?? null) !== 'pending') {
            return;
        }

        $notification = isNotificationActive(null, 'booking', 'notification', 'user');
        $config = business_config('booking', 'notification_settings');
        if ($config->live_values['push_notification_booking']) {
            $repeatOrRegular = $event->booking?->is_repeated ? 'repeat' : 'regular';
            $customer = $event->booking?->customer;
            $title = get_push_notification_message('booking_place', 'customer_notification', $customer?->current_language_key);
            if (isset($customer->fcm_token) && $title && $notification) {
                device_notification(
                    $customer->fcm_token,
                    $title,
                    null,
                    null,
                    $event->booking->id,
                    'booking',
                    null,
                    $customer->id,
                    null,
                    null,
                    $repeatOrRegular
                );
            }
        }
    }
}
