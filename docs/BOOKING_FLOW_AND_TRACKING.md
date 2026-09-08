# Clean365 — Apps, Booking Flow & Order Tracking

Complete reference for the **Customer**, **Supervisor**, and **Serviceman** mobile apps: how a booking is created, assigned, executed, and tracked on the map.

Related Postman / docs:

| File | Purpose |
|------|---------|
| [`docs/postman/LIVE_TRACKING.md`](./postman/LIVE_TRACKING.md) | Short tracking cheat-sheet |
| [`docs/postman/SUPERVISOR_FLOW.md`](./postman/SUPERVISOR_FLOW.md) | Supervisor-only deep dive + response examples |
| [`docs/postman/README.md`](./postman/README.md) | Collection import / regenerate |
| `docs/postman/Clean365-Supervisor-API.postman_collection.json` | Supervisor (folder **06 — Live Tracking**) |
| `docs/postman/Clean365-Serviceman-API.postman_collection.json` | Serviceman |
| `docs/postman/Clean365-Customer-LiveTracking.postman_collection.json` | Customer track / detail |

Base URL: `https://api.clean365.sa` → all mobile paths below are under `/api/v1/...`.

---

## Table of contents

1. [Product model & the three apps](#1-product-model--the-three-apps)
2. [Two status layers](#2-two-status-layers-same-booking)
3. [End-to-end booking flow](#3-end-to-end-booking-flow)
4. [App A — Customer](#4-app-a--customer)
5. [App B — Supervisor](#5-app-b--supervisor)
6. [App C — Serviceman](#6-app-c--serviceman)
7. [Admin (web) touchpoints](#7-admin-web-touchpoints)
8. [Order tracking (detailed)](#8-order-tracking-detailed)
9. [Endpoint quick reference](#9-endpoint-quick-reference)
10. [Config & feature flags](#10-config--feature-flags)
11. [Gaps & what is not built yet](#11-gaps--what-is-not-built-yet)

---

## 1. Product model & the three apps

Clean365 runs in **supervisor mode** (`SUPERVISOR_MODE=true`): providers are **internal supervisors / employees**, not marketplace sellers. Commission, bank, collect-cash, and subscribed-services gates are hidden/disabled for that mode.

| App | User type | Who they are | Primary job |
|-----|-----------|--------------|-------------|
| **Customer** | `customer` | End user | Subscribe / book, pay, cancel/reschedule, track booking status + address |
| **Supervisor** | `provider-admin` | Ops lead for a zone | Run assigned **jobs** (bookings with a team), field stepper, attendance, photos, notes, GPS for admin map |
| **Serviceman** | `provider-serviceman` | Field technician | Solo bookings they own; on **team** jobs they follow supervisor (no status override); GPS for admin map |

### Core vocabulary

| Term | Meaning |
|------|---------|
| **Booking** | One service order (DB `bookings`) |
| **Job / مهمة** | Same booking **after** it has `team_id` — what the supervisor Ops app lists |
| **Readable ID** | Human booking number (e.g. `1000123`) used for guest track |
| **Provider** | Supervisor company/account (`providers`) linked to a `provider-admin` user |
| **Team** | `supervisor_teams` + members; assigned to a booking by **admin** (or auto-assign) |
| **Field status** | Ops stepper on the booking (`assigned` → … → `completed`) |
| **Booking status** | Business pipeline (`pending` → `accepted` → `ongoing` → `completed` / `canceled`) |

There are **no BookingTask checklists**. The booking itself is the unit of work.

### Who may do what

| Action | Customer | Supervisor app | Serviceman app | Admin web |
|--------|----------|----------------|----------------|-----------|
| Place booking | Yes | No | No | Yes (ops) |
| Cancel own booking | Yes (rules) | No | No | Yes |
| Assign supervisor to booking | No | **No** (`403`) | No | Yes (+ auto-assign) |
| Create / assign team | No | View only | No | Yes (+ auto team) |
| Advance `field_status` | No | Yes (ops) | No | Yes |
| Mark team attendance | No | Yes | No | Yes |
| Update `booking_status` on team job | No | Via field sync | **No** (`403`) | Yes |
| Update `booking_status` on solo job | No | Native APIs | Yes | Yes |
| Push live GPS | No | Yes | Yes | Consumes only |
| Guest track by readable_id + phone | Yes | — | — | — |

---

## 2. Two status layers (same booking)

Every booking carries **two** status fields. Apps must not mix them.

| Layer | Column | Values | Audience |
|-------|--------|--------|----------|
| **Business** | `booking_status` | `pending` → `accepted` → `ongoing` → `completed` / `canceled` | Customer, lists, admin, payments |
| **Field ops** | `field_status` | `assigned` → `on_the_way` → `arrived` → `in_progress` → `completed` | Supervisor Ops stepper, admin Live Map “busy” |

### Sync rules (supervisor Ops)

When supervisor calls `PUT /provider/ops/jobs/{id}/field-status`:

| New `field_status` | Effect on `booking_status` | Auto `progress_percent` |
|--------------------|----------------------------|-------------------------|
| `assigned` | stays/forced `accepted` if pending/accepted | `0` |
| `on_the_way` | same | `20` |
| `arrived` | same | `40` |
| `in_progress` | → `ongoing` | `70` |
| `completed` | → `completed` | `100` |

Transitions: forward-only (same step or later). Backward moves return `invalid_field_status_transition`.

Manual override: `PUT .../progress` with `{ "progress_percent": 0–100 }` (does not by itself change statuses).

### Team derived status (Ops teams UI)

| Team chip | Rule |
|-----------|------|
| `available` | Active team, no busy field job |
| `on_the_way` | Active job `field_status = on_the_way` |
| `in_progress` | Active job `arrived` or `in_progress` |
| `unavailable` | `is_active = false` |

---

## 3. End-to-end booking flow

```
┌─────────────────────────────────────────────────────────────────┐
│ CUSTOMER APP                                                     │
│  • Auth / guest                                                  │
│  • Pick service / subscription / address (lat/lng saved)         │
│  • POST /customer/booking/request/send  (or subscription book) │
│  • Booking created: is_paid=1, is_verified=1 (current product)   │
│  • booking_status = pending                                      │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                ┌───────────────▼───────────────┐
                │ AUTO-ASSIGN (if enabled)       │
                │ BOOKING_AUTO_ASSIGN_ENABLED    │
                │ • Pick least-load supervisor   │
                │   (prefer same zone)           │
                │ • Optionally attach free team  │
                │ • booking_status → accepted    │
                │ • field_status → assigned      │
                │   (when team attached)         │
                └───────────────┬───────────────┘
                                │ (or still pending)
                ┌───────────────▼───────────────┐
                │ ADMIN (if needed)              │
                │ • Assign provider / team       │
                │ • Reassign crew until on_the_way│
                └───────────────┬───────────────┘
                                │
┌───────────────────────────────▼─────────────────────────────────┐
│ SUPERVISOR APP (Ops)                                             │
│  • Job appears only when team_id is set                          │
│  • GET /provider/ops/jobs  /  GET .../jobs/{id}                  │
│  • PUT .../seen                                                  │
│  • Field stepper: assigned → on_the_way → arrived →              │
│                   in_progress → completed                        │
│  • Attendance / photos / notes / support                         │
│  • PUT /provider/update-location  (GPS → Admin Live Map)         │
└───────────────────────────────┬─────────────────────────────────┘
                                │
┌───────────────────────────────▼─────────────────────────────────┐
│ SERVICEMAN APP                                                   │
│  • Team jobs: see work via supervisor; cannot change status      │
│  • Solo jobs (no team_id): list/detail + status-update           │
│  • PUT /serviceman/update-location  (GPS → Admin Live Map)       │
└───────────────────────────────┬─────────────────────────────────┘
                                │
┌───────────────────────────────▼─────────────────────────────────┐
│ CUSTOMER APP (during / after)                                    │
│  • Poll GET /customer/booking/{id}                               │
│  • Or guest POST /customer/booking/track/{readable_id}           │
│  • Map: service address pin only (see §8)                        │
│  • booking_status / field_status for stepper UI if shown         │
└─────────────────────────────────────────────────────────────────┘
```

### Phase checklist

| Phase | `booking_status` | `field_status` | Who acts |
|-------|------------------|----------------|----------|
| Created | `pending` | empty / null | Customer |
| Supervisor assigned (auto or admin) | `accepted` | often still empty until team | System / admin |
| Team assigned | `accepted` | `assigned` | Admin / auto |
| En route | `accepted` | `on_the_way` | Supervisor |
| On site | `accepted` | `arrived` | Supervisor |
| Working | `ongoing` | `in_progress` | Supervisor |
| Done | `completed` | `completed` | Supervisor |
| Canceled | `canceled` | — | Customer / admin |

Crew (supervisor/serviceman/team) can be **reassigned until** `field_status` is `on_the_way` (or later busy/completed states). After that, reassignment is locked.

---

## 4. App A — Customer

### Auth

```http
POST /api/v1/customer/auth/login
Content-Type: application/json

{
  "email_or_phone": "+9665...",
  "password": "..."
}
```

Use `Authorization: Bearer {{customer_token}}` on authenticated routes.

Also: registration, social login, logout under `/customer/auth/*`.

### Booking lifecycle APIs

| Method | Path | Auth | Purpose |
|--------|------|------|---------|
| `POST` | `/customer/booking/request/send` | Optional (hitLimiter) | Place a booking request |
| `GET` | `/customer/booking` | Bearer | List my bookings |
| `GET` | `/customer/booking/{id}` | Bearer | Booking detail (address, statuses, provider, …) |
| `GET` | `/customer/booking/single/{id}` | Bearer | Single/repeat variant detail |
| `PUT` | `/customer/booking/status-update/{id}` | Bearer | Customer cancel (`booking_status=canceled`) with rules |
| `PUT` | `/customer/booking/reschedule/{id}` | Bearer | Reschedule |
| `POST` | `/customer/booking/single-repeat-cancel/{repeat_id}` | Bearer | Cancel one repeat visit |
| `POST` | `/customer/booking/track/{readable_id}` | **None** | Guest track (see §8) |
| `GET` | `/customer/booking/{id}/agent-location` | Bearer | Live agent GPS when en route |
| `POST` | `/customer/booking/track/{readable_id}/agent-location` | **None** | Guest live agent GPS |
| `POST` | `/customer/booking/switch-payment-method` | None | Payment method switch |

Subscription booking paths live under customer subscription controllers (same underlying `bookings` + auto-assign hooks).

### What the customer UI should show

1. **Before trip** — booking number, schedule, service, paid badge, address.
2. **During trip** — `booking_status` + optional `field_status` / `progress_percent` if present on payload.
3. **Map** — destination pin from service address; **moving agent pin** from `agent-location` while `available` is true.
4. **Guest track** — enter readable ID + contact phone; then poll guest agent-location.

### Guest track contract

```http
POST /api/v1/customer/booking/track/{readable_id}
Content-Type: application/json

{ "phone": "+9665XXXXXXXX" }
```

Server matches:

- `bookings.readable_id = {readable_id}`
- related `service_address.contact_person_number = phone`

Success → full booking payload (address decoded into `service_address`).  
Mismatch → `404`. Missing phone → `400`.

---

## 5. App B — Supervisor

### Auth

```http
POST /api/v1/provider/auth/login

{
  "email_or_phone": "ops@...",
  "password": "...",
  "type": "email"
}
```

Token → `Authorization: Bearer {{supervisor_token}}`.  
Logout: `POST /api/v1/user/logout`.

Supervisor mode lean profile: `GET /provider/info` (no commission/company marketplace clutter).

### Screen → API map

#### Home — ملخص اليوم

| UI | API |
|----|-----|
| Greeting / role | `GET /provider/info` |
| Bell | `GET /provider/notifications?limit=&offset=` |
| Today cards + % | `GET /provider/ops/dashboard` → `today_summary` |
| Teams preview | same → `teams_preview` |
| FCM | `PUT /provider/update/fcm-token` |

#### Teams — الفرق

| UI | API |
|----|-----|
| List + chips | `GET /provider/ops/teams?status=&search=&limit=&offset=` |
| Detail | `GET /provider/ops/teams/{id}` |

Create/edit/assign team: **admin only** (app may show view-only). Native `PUT /provider/team/assign-booking/{id}` is blocked for supervisors in ops product rules (`403 admin_assignment_only`).

#### Jobs — المهام المسندة

Only bookings with **`team_id`** for this supervisor.

| UI | API |
|----|-----|
| List + counters | `GET /provider/ops/jobs?field_status=&search=&date=&limit=&offset=` |
| Detail | `GET /provider/ops/jobs/{booking_id}` |
| Mark seen | `PUT .../seen` → `is_seen`, `supervisor_seen_at` |
| Stepper | `PUT .../field-status` `{ "field_status": "on_the_way" }` |
| Progress override | `PUT .../progress` `{ "progress_percent": 55 }` |
| Photos | `POST .../images/{before\|during\|after}` multipart `images[]` |
| Notes | `POST .../notes` |
| Attendance | `GET\|POST .../attendance` |
| Support | `POST .../support` |

Job payload always includes customer destination for the map:

```json
"location": { "text": "...", "lat": 24.71, "lng": 46.67 },
"coordinates": { "lat": 24.71, "lng": 46.67 }
```

#### Profile

| UI | API |
|----|-----|
| Profile | `GET /provider/info` |
| Update | `PUT /provider/update/profile` |
| Password | `PUT /provider/update/password` |
| Config | `GET /provider/config` |
| Live GPS | `PUT /provider/update-location` (also in Live Tracking folder) |

### Recommended supervisor job day

1. Login → dashboard.
2. Open unseen job → `PUT .../seen`.
3. Plot customer pin from `coordinates`.
4. Confirm attendance → `POST .../attendance`.
5. `field_status = on_the_way` → start GPS loop (see §8).
6. Upload before/during/after photos as needed.
7. `arrived` → `in_progress` → `completed`.
8. Optional support ticket if blocked.

---

## 6. App C — Serviceman

### Auth

```http
POST /api/v1/serviceman/auth/login

{
  "phone": "+9665...",
  "password": "..."
}
```

Forgot / OTP / reset under `/serviceman/forgot-password`, `otp-verification`, `reset-password`.

### Capabilities

| Feature | Solo booking (`team_id` empty, assigned to them) | Team booking (`team_id` set) |
|---------|--------------------------------------------------|------------------------------|
| List / detail | Yes | Access for visibility (team access rules) |
| `PUT .../booking/status-update` | Yes | **403** supervisor-only |
| Field stepper Ops APIs | No | Supervisor owns stepper |
| Attendance | No | Supervisor marks on booking |
| GPS `update-location` | Yes | Yes |

### Main endpoints

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/serviceman/dashboard` | Home stats |
| `GET` | `/serviceman/info` | Profile |
| `PUT` | `/serviceman/update/profile` | Profile |
| `PUT` | `/serviceman/update/fcm-token` | Push |
| `PUT` | `/serviceman/update-location` | Live GPS → Admin Live Map |
| `GET` | `/serviceman/booking/list` | Bookings |
| `GET` | `/serviceman/booking/detail/{id}` | Detail |
| `PUT` | `/serviceman/booking/status-update/{id}` | Solo status only |
| `GET` | `/serviceman/push-notifications` | Notifications |
| `GET` | `/serviceman/config` | Public config / maps helpers |

Serviceman does **not** drive the Ops field stepper. On team jobs they physically work; the supervisor advances `field_status`.

---

## 7. Admin (web) touchpoints

| Area | Role in flow |
|------|----------------|
| Booking detail | Assign supervisor, assign/change team/crew (until lock), see ops fields |
| Teams | Create teams, members |
| Auto-assign | Optional; least-load supervisor + free team (`config/booking_auto_assign.php`) |
| **Live Map** | `/admin/live-map` + `/admin/live-map/api` — consume agent GPS (see §8) |
| Pending markers | Unassigned `pending` bookings with address coords on the live map |

Admin Live Map is the **only** production consumer of live agent GPS today.

---

## 8. Order tracking (detailed)

This section is the source of truth for **map / GPS / track** behavior across all apps.

### 8.1 Mental model — three maps, not one

```
                    ┌──────────────────────┐
   GPS writers      │  users.latitude      │
   Supervisor ──────►  users.longitude     │
   Serviceman  ──────►  users.last_seen_at  │
                    └──────────┬───────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │  ADMIN LIVE MAP      │  ← only live agent consumer
                    │  poll ~15s           │
                    │  online / busy / HQ  │
                    └──────────────────────┘

   Supervisor Ops
   GET .../ops/jobs/{id}
        │
        ▼
   STATIC customer destination
   (booking service_address lat/lng)
   — supervisor navigates TO the customer —
   — does NOT receive teammate live GPS via API —

   Customer app
   track / booking detail + agent-location
        │
        ▼
   Destination pin + live agent when en route
   (GET/POST .../agent-location)
```

| Map | Moving pin? | Data |
|-----|-------------|------|
| Admin Live Map | Yes (agents) | `users.lat/lng` (+ HQ fallback) |
| Supervisor job map | No (destination only) | Booking `coordinates` |
| Customer track map | Yes (agent) when en route | `.../agent-location` + destination |

---

### 8.2 Data stored for tracking

| Store | Fields | Written by | Read by |
|-------|--------|------------|---------|
| `users` | `latitude`, `longitude`, `last_seen_at` | `PUT .../update-location` | Admin Live Map, Customer `agent-location` |
| `bookings` | `service_address_location` / `service_address` lat-lon | Booking create / address | Supervisor Ops, Customer track/detail, Admin pending markers |
| `bookings` | `field_status`, `progress_percent` | Supervisor Ops | Supervisor UI, customer payload if present, Admin busy calc |
| `providers.coordinates` | HQ lat/lng | Admin provider setup | Live Map fallback when no live GPS |

Config: `config/tracking.php`

| Key | Default | Meaning |
|-----|---------|---------|
| `online_threshold_minutes` | `30` | `last_seen_at` within window → Online |
| `location_write.max_per_minute` | `30` | Throttle → HTTP `429` |
| `busy_field_statuses` | `on_the_way`, `arrived`, `in_progress` | Agent marked **busy** on Live Map |
| `customer_agent_location.enabled` | `true` | Customer may read agent GPS |
| `customer_agent_location.visible_field_statuses` | same as busy | Statuses that unlock customer agent pin |

---

### 8.3 Writer API — push GPS

Shared controller: `FieldAgentLocationController` → `FieldAgentLocationService`.

| Role | Method | Path |
|------|--------|------|
| Supervisor | `PUT` | `/api/v1/provider/update-location` |
| Serviceman | `PUT` | `/api/v1/serviceman/update-location` |

**Auth:** Bearer token; user type must be `provider-admin` or `provider-serviceman`.

**Body (JSON):**

```json
{ "latitude": 24.7136, "longitude": 46.6753 }
```

| Case | Body | Effect |
|------|------|--------|
| Move pin | both lat & lng | Updates coords + `last_seen_at` |
| Heartbeat only | `{}` or omit coords | Refreshes `last_seen_at` only; pin stays |
| Partial | only one of lat/lng | Updates the provided field + `last_seen_at` |

**Validation:** lat ∈ [-90, 90], lng ∈ [-180, 180].

**Response (example):**

```json
{
  "response_code": "default_update_200",
  "message": "successfully updated",
  "content": {
    "latitude": 24.7136,
    "longitude": 46.6753,
    "last_seen_at": "2026-09-06T15:40:00+00:00"
  }
}
```

**Important:**

- GPS write is **not** gated by `field_status` (you can ping anytime).
- Attendance (`POST .../attendance`) is **unrelated** to GPS.
- Middleware may also refresh last-seen on other authenticated field-agent calls; explicit `update-location` is what moves the pin.

#### Recommended app cadence

| Situation | Cadence |
|-----------|---------|
| `field_status` ∈ `on_the_way` \| `arrived` \| `in_progress` | Every **5–10 seconds** with coords |
| Idle / assigned / between jobs | Heartbeat every **2–3 minutes** (`{}`) |
| App background | Platform-dependent; prefer OS background location if product requires continuous admin visibility |
| Hard limit | ≤ **30 writes/minute** |

---

### 8.4 Admin Live Map — consumer logic

| Surface | Path |
|---------|------|
| Page | `GET /admin/live-map` |
| JSON poll | `GET /admin/live-map/api` (typical UI poll ~15s) |

**Agent selection:** active field agents (`provider-admin` + `provider-serviceman`).

**Coordinate resolution (per agent):**

1. Prefer live `users.latitude/longitude` → `location_source: "live"`.
2. Else provider HQ `providers.coordinates` → `location_source: "hq"`.
3. Else agent omitted from map (no pin).

**Online / busy:**

| Status | Rule |
|--------|------|
| `online_available` | `last_seen_at` within online threshold **and** not busy |
| `online_busy` | Online **and** linked to an active booking whose `field_status` ∈ busy list |
| `offline` | Outside threshold (even if coords exist from last ping / HQ) |

**Busy linkage:** for bookings not `completed`/`canceled` with busy `field_status`, mark busy:

- Supervisor user of `booking.provider_id`
- User of `booking.serviceman_id` (solo)
- All serviceman users on `booking.team_id`

**Also on map:** pending booking markers (customer destination coords) for unassigned demand.

Filters on API: `role=supervisor|serviceman`, `zone_id`, `status=online|offline|online_available|online_busy`.

---

### 8.5 Supervisor order map — destination pin

Supervisor does **not** call a “track agent” API. They open the job and plot the **customer**.

```http
GET /api/v1/provider/ops/jobs/{booking_id}
Authorization: Bearer {supervisor_token}
```

Coords extraction order (`SupervisorOpsTrait::getBookingCoordinatesForOps`):

1. `bookings.service_address_location` JSON → `lat`/`latitude` + `lon`/`longitude`/`lng`
2. Else related `service_address.lat` + `service_address.lon`

Exposed as:

```json
{
  "location": { "text": "Riyadh, …", "lat": 24.7136, "lng": 46.6753 },
  "coordinates": { "lat": 24.7136, "lng": 46.6753 }
}
```

List endpoint `GET /provider/ops/jobs` repeats the same shape on each card for multi-marker views.

#### Supervisor tracking UX (recommended)

| Step | Action | API |
|------|--------|-----|
| 1 | Open job, show destination | `GET .../jobs/{id}` |
| 2 | Optional: open external maps / in-app directions to `coordinates` | — |
| 3 | Leave for site | `PUT .../field-status` `{ "field_status": "on_the_way" }` |
| 4 | Start GPS writer loop | `PUT /provider/update-location` |
| 5 | Arrive | `field_status = arrived` |
| 6 | Start work | `field_status = in_progress` (syncs `booking_status` → `ongoing`) |
| 7 | Finish | `field_status = completed` |
| 8 | Stop high-frequency GPS; heartbeat only | `{}` every few minutes |

While steps 3–6 run, **Admin Live Map** shows the supervisor (and team members who also ping) as **online_busy**.

Supervisor app **does not** currently receive live GPS of team members for the same booking via a dedicated endpoint. If the product needs “watch my team on my phone”, that is a **new API** (not shipped).

---

### 8.6 Customer order tracking

#### Live socket (preferred)

1. Read `broadcasting` from `GET /customer/config`.
2. Auth channel: `POST /api/broadcasting/auth` with Bearer token.
3. Subscribe `private-booking.{bookingId}.tracking` when en route.
4. Listen `.BookingAgentLocationUpdated` for `agent.latitude/longitude`.
5. Listen `.BookingTrackingEnded` to hide the pin.
6. Session starts when supervisor sets `field_status` to `on_the_way` (or later tracking statuses).

#### Authenticated agent location (HTTP fallback)

```http
GET /api/v1/customer/booking/{booking_id}/agent-location
Authorization: Bearer {customer_token}
```

#### Guest agent location

```http
POST /api/v1/customer/booking/track/{readable_id}/agent-location
{ "phone": "<service_address.contact_person_number>" }
```

**`available: true` only when all of:**

1. `CUSTOMER_AGENT_LOCATION_ENABLED` (default true)
2. `booking_status` not `completed` / `canceled`
3. `field_status` ∈ `on_the_way` | `arrived` | `in_progress`
4. Primary agent (supervisor `provider.owner`, else assigned `serviceman.user`) has live lat/lng

Otherwise HTTP 200 with `available: false` and `reason` (`not_en_route`, `no_gps`, `no_agent`, `booking_closed`, `disabled`).

Poll every **5–10s** while `tracking_active` is true. Use `destination` for the home pin and `agent` for the moving pin.

#### Authenticated booking detail

```http
GET /api/v1/customer/booking/{booking_id}
Authorization: Bearer {customer_token}
```

Use for:

- Status chips: `booking_status`, and `field_status` / `progress_percent` when present
- Map destination fallback: decode `service_address`

#### Guest track (booking payload)

```http
POST /api/v1/customer/booking/track/{readable_id}
{ "phone": "<service_address.contact_person_number>" }
```

Same address/status payload idea; no login. Then call guest agent-location for the moving pin.

---

### 8.7 Serviceman tracking role

Serviceman only **writes** GPS:

```http
PUT /api/v1/serviceman/update-location
{ "latitude": ..., "longitude": ... }
```

Same cadence rules as supervisor. On team jobs they should still ping so Admin Live Map shows the crew; status progression stays with the supervisor.

---

### 8.8 Field status × tracking matrix

| `field_status` | Customer-facing meaning | Customer agent pin | Supervisor map | Admin agent color | GPS loop |
|----------------|-------------------------|--------------------|----------------|-------------------|----------|
| *(empty)* | Waiting assignment | hidden (`not_en_route`) | — | available / offline | Heartbeat |
| `assigned` | Team assigned | hidden (`not_en_route`) | Show destination | available (not busy) | Heartbeat |
| `on_the_way` | Team en route | **shown if GPS** | Destination + navigate | **busy** | **5–10s** |
| `arrived` | At property | **shown if GPS** | Destination | **busy** | **5–10s** |
| `in_progress` | Cleaning / work | **shown if GPS** | Destination | **busy** | **5–10s** |
| `completed` | Done | hidden (`booking_closed`) | Optional | available again | Heartbeat |

---

### 8.9 Sequence diagram — one order tracked end-to-end

```
Customer          API              Auto/Admin         Supervisor         Serviceman         Admin Live Map
   │               │                   │                   │                  │                   │
   │ place booking │                   │                   │                  │                   │
   ├──────────────►│                   │                   │                  │                   │
   │               │ auto-assign       │                   │                  │                   │
   │               ├──────────────────►│                   │                  │                   │
   │               │ accepted+team     │                   │                  │                   │
   │               │───────────────────────────────────────►│                  │                   │
   │               │                   │                   │ jobs list        │                   │
   │               │◄──────────────────────────────────────┤                  │                   │
   │               │                   │                   │ seen + on_the_way│                   │
   │               │◄──────────────────────────────────────┤                  │                   │
   │               │                   │                   │ GPS ping ────────┼───────────────────►│
   │               │                   │                   │                  │ GPS ping ─────────►│
   │ poll/track    │                   │                   │                  │                   │
   ├──────────────►│ status+address    │                   │                  │                   │
   │◄──────────────┤                   │                   │                  │                   │
   │ (status UI)   │                   │                   │ arrived / work   │                   │
   │               │◄──────────────────────────────────────┤                  │                   │
   │               │                   │                   │ completed        │                   │
   │               │◄──────────────────────────────────────┤                  │                   │
   │ final poll    │                   │                   │                  │                   │
   ├──────────────►│ completed         │                   │                  │                   │
```

---

### 8.10 Error / edge cases (tracking)

| Case | Behavior |
|------|----------|
| Rate limit exceeded | `429` on `update-location` |
| Non field-agent token | `403` on `update-location` |
| No GPS ever sent | Admin may show HQ pin + Offline |
| Job without coords | Supervisor map has null `coordinates` — hide map or prompt address fix |
| Guest track wrong phone | `404` |
| Serviceman status on team job | `403` — use supervisor stepper |
| Reassign after `on_the_way` | Locked in admin helpers |

---

### 8.11 Postman folders for tracking

| Collection | What to run |
|------------|-------------|
| Supervisor **06 — Live Tracking** | Job detail pin → jobs list → field-status on_the_way → GPS → heartbeat |
| Customer Live Tracking | Guest track + detail + **04 Agent Live Location** (HTTP); prefer Reverb sockets in app |
| Serviceman **Home & Profile** | `update-location` |

### Live sockets (Reverb)

| Env | Purpose |
|-----|---------|
| `BROADCAST_DRIVER=reverb` | Enable broadcasting |
| `REVERB_APP_*` / `REVERB_HOST` / `PORT` / `SCHEME` | Reverb server |
| `TRACKING_BROADCAST_ENABLED` | Toggle tracking events |
| Run | `php artisan reverb:start` |

Channels: `private-booking.{id}.tracking`, `private-admin.live-map`. Details: [`docs/postman/LIVE_TRACKING.md`](./postman/LIVE_TRACKING.md).

---

## 9. Endpoint quick reference

### Customer

| Method | Path |
|--------|------|
| `POST` | `/customer/auth/login` |
| `POST` | `/customer/booking/request/send` |
| `GET` | `/customer/booking` |
| `GET` | `/customer/booking/{id}` |
| `GET` | `/customer/booking/{id}/agent-location` |
| `PUT` | `/customer/booking/status-update/{id}` |
| `PUT` | `/customer/booking/reschedule/{id}` |
| `POST` | `/customer/booking/track/{readable_id}` |
| `POST` | `/customer/booking/track/{readable_id}/agent-location` |

### Supervisor

| Method | Path |
|--------|------|
| `POST` | `/provider/auth/login` |
| `GET` | `/provider/info` |
| `GET` | `/provider/ops/dashboard` |
| `GET` | `/provider/ops/teams` |
| `GET` | `/provider/ops/teams/{id}` |
| `GET` | `/provider/ops/jobs` |
| `GET` | `/provider/ops/jobs/{id}` |
| `PUT` | `/provider/ops/jobs/{id}/seen` |
| `PUT` | `/provider/ops/jobs/{id}/field-status` |
| `PUT` | `/provider/ops/jobs/{id}/progress` |
| `POST` | `/provider/ops/jobs/{id}/images/{stage}` |
| `POST` | `/provider/ops/jobs/{id}/notes` |
| `GET\|POST` | `/provider/ops/jobs/{id}/attendance` |
| `POST` | `/provider/ops/jobs/{id}/support` |
| `PUT` | `/provider/update-location` |
| `PUT` | `/provider/update/fcm-token` |

### Serviceman

| Method | Path |
|--------|------|
| `POST` | `/serviceman/auth/login` |
| `GET` | `/serviceman/dashboard` |
| `GET` | `/serviceman/info` |
| `GET` | `/serviceman/booking/list` |
| `GET` | `/serviceman/booking/detail/{id}` |
| `PUT` | `/serviceman/booking/status-update/{id}` |
| `PUT` | `/serviceman/update-location` |

### Admin (web)

| Method | Path |
|--------|------|
| `GET` | `/admin/live-map` |
| `GET` | `/admin/live-map/api` |

---

## 10. Config & feature flags

| Config / env | Effect |
|--------------|--------|
| `SUPERVISOR_MODE` / `config/supervisor.php` | Providers = internal supervisors |
| `BOOKING_AUTO_ASSIGN_ENABLED` | Auto pick supervisor (+ optional free team) on create |
| `config/booking_auto_assign.php` | Zone preference, busy team statuses, picker class |
| `config/tracking.php` | Online window, GPS rate limit, busy statuses, customer agent-location |
| `CUSTOMER_AGENT_LOCATION_ENABLED` | Toggle customer live agent GPS HTTP API |
| `TRACKING_BROADCAST_ENABLED` | Toggle Reverb location events |
| `BROADCAST_DRIVER=reverb` | Use Laravel Reverb for sockets |
| Product rules | Bookings forced paid + verified in current build |

---

## 11. Gaps & what is not built yet

Document these explicitly so mobile teams do not assume APIs exist:

1. **No supervisor live view of teammate GPS** for a booking (customer agent-location + booking channel are shipped).
2. **No GPS history / polyline trail** storage API.
3. **No FCM for continuous location** — use Reverb sockets (or HTTP poll); FCM remains for notifications only.
4. **BookingTask** module / checklist APIs — removed from the ops product; do not use.

Customer agent location is implemented:

- `GET /customer/booking/{id}/agent-location`
- `POST /customer/booking/track/{readable_id}/agent-location`
- Live sockets via Reverb (`BookingTrackingStarted` / `BookingAgentLocationUpdated` / `BookingTrackingEnded`)

Saudi dummy GPS:

```bash
php artisan db:seed --class="Modules\\ServicemanModule\\Database\\Seeders\\SaudiFieldAgentLocationsSeeder"
```

---

## Document maintenance

- Prefer updating this file when booking or tracking contracts change.
- Regenerate Postman after Ops/GPS route changes:

```bash
python3 docs/postman/generate_collection.py
```

- Keep `LIVE_TRACKING.md` as the short cheat-sheet; keep this file as the full cross-app narrative.
