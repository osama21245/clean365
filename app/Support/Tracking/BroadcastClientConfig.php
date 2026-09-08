<?php

namespace App\Support\Tracking;

/**
 * Public broadcasting connection details for mobile / admin Echo clients.
 */
class BroadcastClientConfig
{
    public static function enabled(): bool
    {
        if (!(bool) config('tracking.broadcast.enabled', true)) {
            return false;
        }

        $driver = (string) config('broadcasting.default', 'null');

        return in_array($driver, ['reverb', 'pusher'], true)
            && filled(config('broadcasting.connections.'.$driver.'.key'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function forClients(): array
    {
        $driver = (string) config('broadcasting.default', 'null');
        $enabled = self::enabled();

        $host = (string) env('REVERB_HOST', '');
        $port = (int) env('REVERB_PORT', 8080);
        $scheme = (string) env('REVERB_SCHEME', 'http');

        if ($driver === 'pusher') {
            $host = (string) env('PUSHER_HOST', '');
            $port = (int) env('PUSHER_PORT', 443);
            $scheme = (string) env('PUSHER_SCHEME', 'https');
        }

        return [
            'enabled' => $enabled,
            'driver' => $driver,
            'key' => $enabled ? config('broadcasting.connections.'.$driver.'.key') : null,
            'host' => $enabled ? $host : null,
            'port' => $enabled ? $port : null,
            'scheme' => $enabled ? $scheme : null,
            'auth_endpoint' => url('/api/broadcasting/auth'),
            'web_auth_endpoint' => url('/broadcasting/auth'),
            'channels' => [
                'booking_tracking' => 'booking.{bookingId}.tracking',
                'admin_live_map' => 'admin.live-map',
            ],
            'events' => [
                'started' => 'BookingTrackingStarted',
                'location' => 'BookingAgentLocationUpdated',
                'ended' => 'BookingTrackingEnded',
            ],
        ];
    }
}
