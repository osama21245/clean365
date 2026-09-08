<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('booking.{bookingId}.tracking', function (User $user, string $bookingId) {
    $booking = Booking::query()
        ->select(['id', 'customer_id', 'provider_id', 'serviceman_id', 'team_id'])
        ->find($bookingId);

    if (!$booking) {
        return false;
    }

    if (in_array($user->user_type, ['super-admin', 'admin-employee'], true)) {
        return [
            'id' => $user->id,
            'role' => 'admin',
        ];
    }

    if ((string) $booking->customer_id === (string) $user->id) {
        return [
            'id' => $user->id,
            'role' => 'customer',
        ];
    }

    if ($user->user_type === 'provider-admin' && $booking->provider_id) {
        $owns = Provider::query()
            ->where('id', $booking->provider_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($owns) {
            return [
                'id' => $user->id,
                'role' => 'supervisor',
            ];
        }
    }

    if ($user->user_type === 'provider-serviceman') {
        $servicemanId = Serviceman::query()->where('user_id', $user->id)->value('id');
        if (!$servicemanId) {
            return false;
        }

        if ((string) $booking->serviceman_id === (string) $servicemanId) {
            return [
                'id' => $user->id,
                'role' => 'serviceman',
            ];
        }

        if ($booking->team_id) {
            $onTeam = DB::table('supervisor_team_servicemen')
                ->where('team_id', $booking->team_id)
                ->where('serviceman_id', $servicemanId)
                ->exists();

            if ($onTeam) {
                return [
                    'id' => $user->id,
                    'role' => 'serviceman',
                ];
            }
        }
    }

    return false;
});

Broadcast::channel('admin.live-map', function (User $user) {
    if (in_array($user->user_type, ['super-admin', 'admin-employee'], true)) {
        return [
            'id' => $user->id,
            'role' => 'admin',
        ];
    }

    return false;
});
