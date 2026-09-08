# Booking live tracking (map)

How map / location tracking works today for **customer**, **supervisor**, and **admin**.

> Full cross-app guide: [`../BOOKING_FLOW_AND_TRACKING.md`](../BOOKING_FLOW_AND_TRACKING.md)

## Architecture

| Layer | What it does |
|-------|----------------|
| **Writer** | Supervisor / serviceman `PUT .../update-location` → `users.lat/lng/last_seen_at` + **Reverb broadcast** |
| **Session start** | `PUT .../field-status` → `on_the_way`\|`arrived`\|`in_progress` → `BookingTrackingStarted` |
| **Customer** | Subscribe `private-booking.{id}.tracking` **or** poll `GET .../agent-location` |
| **Admin Live Map** | Subscribe `private-admin.live-map` + HTTP poll fallback |
| **Supervisor map** | Static customer destination from ops jobs |

```
Supervisor PUT field-status (on_the_way)
        → BookingTrackingStarted  →  customer + admin sockets

Supervisor PUT update-location (loop)
        → users GPS + BookingAgentLocationUpdated
                → customer map pin
                → admin live-map pin

Supervisor PUT field-status (completed)
        → BookingTrackingEnded
```

---

## Live sockets (Laravel Reverb)

### Ops

```bash
php artisan reverb:start
# ensure BROADCAST_DRIVER=reverb and REVERB_* are set
```

### Channels

| Channel | Who may auth | Events |
|---------|--------------|--------|
| `private-booking.{bookingId}.tracking` | Customer owner, assigned supervisor, team serviceman, platform admin | `BookingTrackingStarted`, `BookingAgentLocationUpdated`, `BookingTrackingEnded` |
| `private-admin.live-map` | `super-admin`, `admin-employee` | same events |

Auth endpoints:

- Mobile: `POST /api/broadcasting/auth` (Bearer)
- Admin web: `POST /broadcasting/auth` (session + CSRF)

Public Echo settings are on app config: `content.broadcasting` from:

- `GET /api/v1/customer/config`
- `GET /api/v1/provider/config`
- `GET /api/v1/serviceman/config`

### Customer app subscribe (sketch)

1. Read `broadcasting` from config (`enabled`, `key`, `host`, `port`, `scheme`, `auth_endpoint`).
2. When booking `field_status` enters tracking (or after `BookingTrackingStarted`), Echo:

```js
Echo.private(`booking.${bookingId}.tracking`)
  .listen('.BookingAgentLocationUpdated', (e) => {
    // e.agent.latitude / longitude → map pin
  })
  .listen('.BookingTrackingEnded', () => { /* hide pin */ });
```

3. Fallback: poll `GET /customer/booking/{id}/agent-location` every 5–10s if socket down.

### When streaming is active

Same as HTTP agent-location:

- `field_status` ∈ `on_the_way` | `arrived` | `in_progress`
- booking not completed/canceled
- GPS writes throttled (~3s / ~15m) via `TRACKING_BROADCAST_*`

`booking_status` becomes `ongoing` at `in_progress`; streaming already starts at `on_the_way`.

---

## HTTP endpoints (still required)

### Supervisor

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/provider/ops/jobs/{id}` | Customer destination pin |
| `PUT` | `/provider/ops/jobs/{id}/field-status` | Starts/ends socket session |
| `PUT` | `/provider/update-location` | GPS write + broadcast |

### Customer

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/customer/booking/{id}/agent-location` | Poll fallback |
| `POST` | `/customer/booking/track/{readable_id}/agent-location` | Guest poll fallback |

### Admin

| Path | Purpose |
|------|---------|
| `/admin/live-map` | UI (Echo + poll) |
| `/admin/live-map/api` | Poll JSON |

---

## Saudi dummy GPS seeder

```bash
php artisan db:seed --class="Modules\\ServicemanModule\\Database\\Seeders\\SaudiFieldAgentLocationsSeeder"
```

Sets `users.latitude/longitude/last_seen_at` for active supervisors + servicemen across KSA cities (also HQ coords when missing).

---

## Postman

| Collection | Notes |
|------------|-------|
| Supervisor **06 — Live Tracking** | field-status + update-location (triggers sockets when Reverb runs) |
| Customer Live Tracking | Prefer socket; HTTP agent-location = fallback |
