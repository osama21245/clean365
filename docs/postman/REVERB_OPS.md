# Live location sockets — ops checklist

## Server

1. Packages: `laravel/reverb`, `pusher/pusher-php-server` (already in composer).
2. `.env`:
   - `BROADCAST_DRIVER=reverb`
   - `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET`
   - `REVERB_HOST` = public hostname clients use (not `localhost` in production)
   - `REVERB_PORT` / `REVERB_SCHEME` (`https` + `443` behind TLS proxy recommended)
   - `TRACKING_BROADCAST_ENABLED=true`
3. Start Reverb (supervisor/systemd):

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

4. Seed demo pins (optional):

```bash
php artisan db:seed --class="Modules\\ServicemanModule\\Database\\Seeders\\SaudiFieldAgentLocationsSeeder"
```

## Flow

1. Supervisor `PUT .../field-status` → `on_the_way` → `BookingTrackingStarted`
2. Supervisor `PUT .../update-location` loop → `BookingAgentLocationUpdated`
3. Customer Echo: `private-booking.{id}.tracking`
4. Admin Live Map Echo: `private-admin.live-map` (+ HTTP poll fallback)
5. `completed` → `BookingTrackingEnded`

Details: [`LIVE_TRACKING.md`](./LIVE_TRACKING.md)
