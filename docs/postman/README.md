# Clean365 Supervisor & Serviceman API — Postman

## Files

| File | Purpose |
|------|---------|
| `Clean365-Supervisor-API.postman_collection.json` | Supervisor / Ops app collection |
| `Clean365-Supervisor-Local.postman_environment.json` | Supervisor environment vars |
| `Clean365-Serviceman-API.postman_collection.json` | Serviceman (technician) app collection |
| `Clean365-Serviceman-Local.postman_environment.json` | Serviceman environment vars |
| `Clean365-Customer-LiveTracking.postman_collection.json` | Customer track + booking detail (map pin / status) |
| `Clean365-Customer-LiveTracking.postman_environment.json` | Customer live-tracking env vars |
| `SUPERVISOR_FLOW.md` | Full supervisor flow guide (screens ↔ APIs) |
| `LIVE_TRACKING.md` | Map / GPS architecture (supervisor + customer + admin) |
| [`../BOOKING_FLOW_AND_TRACKING.md`](../BOOKING_FLOW_AND_TRACKING.md) | **Full** guide: all 3 apps + booking flow + detailed order tracking |
| `response_fixtures.py` | Example response payloads |
| `generate_collection.py` | Regenerate supervisor + serviceman collections after API changes |

## Import into Postman

1. Open Postman → **Import**
2. Import the **Supervisor** collection + environment
3. Import the **Serviceman** collection + environment
4. Import **Customer Live Tracking** collection + environment (map / track only)
5. Select the matching environment and fill credentials / IDs

## Supervisor quick start

1. Run **01 — Auth → Supervisor Login**
2. Token saved to `{{supervisor_token}}`
3. Use folders **02–06** (Home / Teams / Bookings / Profile / Live Tracking)

### Supervisor structure

```
01 — Auth
02 — Home                 ops/dashboard + notifications
03 — Teams                ops/teams + view-only team/serviceman
04 — Bookings             ops/jobs (seen, field-status, progress, attendance, photos, notes, support)
05 — Profile              info, FCM, update-location
06 — Live Tracking        job map pin + GPS ping + field-status → on_the_way
07 — Errors
```

### Ops endpoints

| Method | Endpoint | Notes |
|--------|----------|--------|
| GET | `/provider/ops/dashboard` | Today summary |
| GET | `/provider/ops/teams` | Teams + status chips |
| GET | `/provider/ops/jobs` | Assigned jobs (`team_id` required) |
| GET | `/provider/ops/jobs/{id}` | Job detail (**no `tasks[]`**) + customer `coordinates` |
| PUT | `/provider/ops/jobs/{id}/seen` | Mark seen |
| PUT | `/provider/ops/jobs/{id}/field-status` | Stepper (+ auto progress %) |
| PUT | `/provider/ops/jobs/{id}/progress` | Manual progress override |
| POST | `/provider/ops/jobs/{id}/images/{stage}` | before / during / after |
| POST | `/provider/ops/jobs/{id}/notes` | Execution notes |
| GET\|POST | `/provider/ops/jobs/{id}/attendance` | Mark member `is_attended` |
| POST | `/provider/ops/jobs/{id}/support` | Support / issue |
| PUT | `/provider/update-location` | Live GPS for admin Live Map |

### Live location (supervisor + serviceman)

Powers **Admin → Live Map**. Supervisor job map uses **static customer** coords from ops jobs. Customer “track” is booking lookup — **not** agent GPS. See [`LIVE_TRACKING.md`](./LIVE_TRACKING.md).

| Role | Endpoint |
|------|----------|
| Supervisor | `PUT /api/v1/provider/update-location` |
| Serviceman | `PUT /api/v1/serviceman/update-location` |

```json
{ "latitude": 24.7136, "longitude": 46.6753 }
```

- Omit coords for heartbeat-only (`{}`) — still refreshes `last_seen_at`
- Cadence: **5–10s** on active job; **2–3 min** idle heartbeat
- Rate limit ~30/min → `429`
- Env vars: `{{lat}}`, `{{lng}}`

**Removed (do not use):** BookingTask CRUD, serviceman GPS attendance check-in.

## Customer live tracking (small collection)

Import `Clean365-Customer-LiveTracking.*`.

| Method | Endpoint | Notes |
|--------|----------|--------|
| `POST` | `/customer/booking/track/{readable_id}` | Guest; body `{ "phone": "..." }` must match address contact |
| `GET` | `/customer/booking/{id}` | Auth; poll status + service_address pin |
| `GET` | `/customer/booking` | Auth list |
| `GET` | `/customer/booking/{id}/agent-location` | Auth; live supervisor/agent GPS when en route (HTTP fallback) |
| `POST` | `/customer/booking/track/{readable_id}/agent-location` | Guest; same GPS rules + phone check |
| Config | `content.broadcasting` on `/customer/config` | Echo/Reverb connection for live socket |

**Prefer sockets** when `broadcasting.enabled`; poll agent-location as fallback. Streaming while `field_status` ∈ `on_the_way`\|`arrived`\|`in_progress`.

## Serviceman quick start

1. Run **01 — Auth → Serviceman Login**
2. Token saved to `{{serviceman_token}}`
3. Use Home / Bookings / Notifications

### Serviceman structure

```
01 — Auth                 login, forgot/OTP/reset, logout
02 — Home & Profile       dashboard, info, FCM, update-location (live map only)
03 — Bookings             list/detail/status (solo only; team = supervisor-only)
04 — Notifications
05 — Errors
```

**No attendance / task APIs** on serviceman — supervisor marks attendance on the booking.

## Auth header

```
Authorization: Bearer {{supervisor_token|serviceman_token|customer_token}}
Accept: application/json
```

## Two status layers (supervisor jobs)

| Layer | Field | Flow |
|-------|--------|------|
| Business | `booking_status` | pending → accepted → ongoing → completed / canceled |
| Field Ops | `field_status` | assigned → on_the_way → arrived → in_progress → completed |

Progress % auto: assigned=0, on_the_way=20, arrived=40, in_progress=70, completed=100.

UI **مهمة** = Booking with `team_id` (the booking itself is the job).

## Regenerate

```bash
python3 docs/postman/generate_collection.py
```

See [`SUPERVISOR_FLOW.md`](./SUPERVISOR_FLOW.md) for the full narrative.
