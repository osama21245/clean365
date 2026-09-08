# Clean365 Supervisor Flow Guide

This document explains the **full supervisor journey** from authentication through field completion, how it relates to the **native customer booking pipeline**, and which APIs power each mobile screen.

Related files:

| File | Purpose |
|------|---------|
| [`Clean365-Supervisor-API.postman_collection.json`](./Clean365-Supervisor-API.postman_collection.json) | Postman collection (examples + saved responses) |
| [`Clean365-Supervisor-Local.postman_environment.json`](./Clean365-Supervisor-Local.postman_environment.json) | Environment variables |
| [`README.md`](./README.md) | Import / regenerate notes |

---

## 1. Product model (quick)

- **Provider = Supervisor** (internal ops lead), not a marketplace seller.
- **Serviceman = field technician**.
- Customer **subscribes / books** first. Supervisor work starts **after** the booking exists.
- In the mobile UI, **مهمة (task/job) = Booking** that already has a team (`team_id`).
- Optional **BookingTask** rows are a **sub-checklist** under that booking (drive progress %).

### Who does what

| Action | Admin dashboard | Supervisor app |
|--------|-----------------|----------------|
| Assign supervisor to a booking | Yes | No (`403`) |
| Create team / assign team to booking | Yes | View only |
| Create task / assign servicemen to task | Yes | No (`403`) |
| See teams and which booking they are on | Yes | Yes |
| Change task status | Yes | Yes |
| Add task notes + before/after photos | Yes | Yes |
| Field stepper (`field_status`) | — | Yes |
| Accept/reject incoming booking | Yes (assign) | **No** |

---

## 2. Two status layers (same booking)

| Layer | Field | Values | Who cares |
|-------|--------|--------|-----------|
| Business pipeline | `booking_status` | `pending` → `accepted` → `ongoing` → `completed` / `canceled` | Customer app, admin, booking lists |
| Field ops stepper | `field_status` | `assigned` → `on_the_way` → `arrived` → `in_progress` → `completed` | Supervisor Ops app |

### Sync rules

| When `field_status` becomes… | `booking_status` becomes… |
|------------------------------|---------------------------|
| `assigned` / `on_the_way` / `arrived` | stays/forced `accepted` if still pending/accepted |
| `in_progress` | `ongoing` |
| `completed` | `completed` |

Accept / assign team sets `field_status = assigned` (supervisor mode).

### Team derived status (Ops teams screen)

| Team `status` | Rule |
|---------------|------|
| `available` | Active team, no busy field job |
| `on_the_way` | Active job `field_status = on_the_way` |
| `in_progress` | Active job `arrived` or `in_progress` |
| `unavailable` | Team `is_active = false` |

---

## 3. End-to-end flow (customer → supervisor → complete)

```
Customer places booking
        │
        ▼
booking_status = pending
        │
ADMIN dashboard
  • Assign supervisor (provider) to the booking
  • Assign the chosen team
  • Create tasks and assign team members
        │
        ▼
booking_status = accepted
field_status   = assigned
team_id + tasks set
        │
Supervisor app (view + execute only)
GET /provider/ops/jobs
GET /provider/team/          (see which team is on which booking)
GET /provider/booking-task/…
        │
Field stepper
assigned → on_the_way → arrived → in_progress → completed
PUT /provider/ops/jobs/{id}/field-status
        │
Task status / notes / before-after photos
PUT  /provider/booking-task/{id}/status
PUT  /provider/booking-task/{id}/notes
POST /provider/booking-task/{id}/before-images
POST /provider/booking-task/{id}/after-images
        │
        ▼
field_status = completed
booking_status = completed
```

Supervisor **cannot** accept a booking, create/delete tasks, assign servicemen, or create/assign teams. Those are admin-only (`403` `admin_assignment_only_403`).

---

## 4. Screen → API map (matches app UI)

### Home — ملخص اليوم

| UI | API |
|----|-----|
| Greeting / مشرف badge | `GET /provider/info` (lean: name, role, phone, avatar, zone name — no commission/company/zone polygon) |
| Notification bell | `GET /provider/notifications?limit=&offset=` |
| Today cards (مكتملة / قادمة / جارية / مهام اليوم) + % | `GET /provider/ops/dashboard` → `today_summary` |
| حالة الفرق الحالية | `teams_preview` on same dashboard |
| Register device | `PUT /provider/update/fcm-token` |

**Lean responses (supervisor mode):** `/provider/info`, booking list/detail, team CRUD, serviceman list/show, booking-tasks, dashboard `recent_bookings` / `serviceman_list`, and all `/provider/ops/*` strip wallet, commission, company marketplace fields, and zone polygons.


### Teams — الفرق

| UI | API |
|----|-----|
| Summary chips | `GET /provider/ops/teams` → `summary` |
| Filters / search | `?status=&search=&limit=&offset=` |
| Team card + current job | list `teams.data[]` |
| View details | `GET /provider/ops/teams/{id}` |

### Assigned jobs — المهام المسندة

| UI | API |
|----|-----|
| Status counters + today % | `GET /provider/ops/jobs` → `summary` |
| Filters / search | `?field_status=&search=&date=` |
| Job card (service, package, team, customer, time, location, 2/3, 65%) | `jobs.data[]` (`tasks_summary`, `progress_percent`) |
| View details | `GET /provider/ops/jobs/{booking_id}` |

### Job detail — تفاصيل المهمة

| UI | API |
|----|-----|
| Stepper timeline | `field_status` + `field_status_timeline` |
| Update status button | `PUT .../field-status` `{ "field_status": "..." }` |
| Progress bar | `progress_percent` (+ auto from tasks) |
| Customer block / map | `customer`, `location_text`, `coordinates` |
| Assigned team + attendance | `team`, `attendance` / `GET .../attendance` |
| Customer note | `customer_note` |
| Photo stages | `photos.before|during|after` + `photos.counts` |
| Upload photos | `POST .../images/{stage}` multipart `images[]` |
| Execution notes | `execution_notes` + `POST .../notes` |
| Support / issue / attachment | `POST .../support` `type=support|operational_issue|attachment` |

### Profile tab

| UI | API |
|----|-----|
| Profile | `GET /provider/info` (supervisor mode returns lean profile) |
| Update profile | `PUT /provider/update/profile` — form-data: `first_name` (req), `last_name`, `phone` (req), `email`, optional `profile_image` (no company fields) |
| Change password | `PUT /provider/update/password` |
| App config | `GET /provider/config` (public) |

---

## 5. Auth

### Supervisor

```http
POST {{base_url}}/api/v1/provider/auth/login
Content-Type: application/json

{
  "email_or_phone": "ops.test@clean365.sa",
  "password": "Clean365Ops@2026",
  "type": "email"
}
```

Response `content.token` → header `Authorization: Bearer {{supervisor_token}}`.

Logout: `POST /api/v1/user/logout`.

Default Postman `base_url`: `https://api.clean365.sa`.

### Serviceman

```http
POST {{base_url}}/api/v1/serviceman/auth/login

{
  "phone": "+201000000001",
  "password": "password123"
}
```

---

## 6. Endpoint catalog (supervisor-related)

### Auth & profile

| Method | Path | Notes |
|--------|------|-------|
| POST | `/provider/auth/login` | Supervisor login |
| POST | `/user/logout` | Logout |
| GET | `/provider/info` | Lean profile (role, name, phone, avatar, zone name) |
| PUT | `/provider/update/profile` | Update profile |
| PUT | `/provider/update/password` | Password |
| PUT | `/provider/update/fcm-token` | Push token |
| GET | `/provider/notifications` | Bell list (`limit`,`offset`) |
| GET | `/provider/config` | Public config |

### Servicemen & teams (setup)

| Method | Path | Notes |
|--------|------|-------|
| GET | `/provider/serviceman/` | List (view only) |
| GET | `/provider/serviceman/{id}` | Show (view only) |
| GET | `/provider/team/` | List (view only) + assigned bookings |
| GET | `/provider/team/{id}` | Show (view only) |

> Serviceman / team / task **create, assign, delete**: **admin dashboard only**. Supervisor API returns `403` (`admin_assignment_only_403` / `serviceman_admin_only_403`).

### Booking pipeline (native)

| Method | Path | Notes |
|--------|------|-------|
| POST | `/provider/booking/` | List (body filters) |
| GET | `/provider/booking/{id}` | Detail |
| PUT | `/provider/booking/request-accept/{id}` | **403** — admin assigns supervisor |
| PUT | `/provider/booking/status-update/{id}` | Manual pipeline status |
| PUT | `/provider/booking/assign-serviceman/{id}` | **403** — admin assigns team members |

### Booking tasks (sub-checklist)

| Method | Path | Notes |
|--------|------|-------|
| GET | `/provider/booking-task/booking/{booking_id}` | List |
| GET | `/provider/booking-task/{task_id}` | Show |
| POST/DELETE | `/provider/booking-task/…` | **403** — admin creates/deletes |
| PUT | `/provider/booking-task/{task_id}/status` | Supervisor status |
| PUT | `/provider/booking-task/{task_id}/notes` | Supervisor notes |
| POST | `/provider/booking-task/{task_id}/before-images` | Before photos |
| POST | `/provider/booking-task/{task_id}/after-images` | After photos |

### Ops (mobile field app)

| Method | Path | Notes |
|--------|------|-------|
| GET | `/provider/ops/dashboard` | Home |
| GET | `/provider/ops/teams` | Teams list |
| GET | `/provider/ops/teams/{id}` | Team detail |
| GET | `/provider/ops/jobs` | Assigned jobs |
| GET | `/provider/ops/jobs/{booking_id}` | Job detail |
| PUT | `/provider/ops/jobs/{id}/field-status` | Stepper |
| PUT | `/provider/ops/jobs/{id}/progress` | Manual % if no tasks |
| POST | `/provider/ops/jobs/{id}/images/{stage}` | before\|during\|after |
| POST | `/provider/ops/jobs/{id}/notes` | Execution note |
| GET | `/provider/ops/jobs/{id}/attendance` | Attendance summary |
| POST | `/provider/ops/jobs/{id}/support` | Support tickets |

### Serviceman (field)

| Method | Path | Notes |
|--------|------|-------|
| GET | `/serviceman/booking/list` | My bookings |
| GET | `/serviceman/booking/detail/{id}` | Detail (status read-only if team) |
| GET | `/serviceman/task/` | My tasks |
| GET | `/serviceman/task/{id}` | Task detail |
| POST | `/serviceman/task/{id}/check-in` | Attendance (1 km) |
| PUT | `/serviceman/task/{id}/status` | **403** supervisor only |
| PUT | `/serviceman/task/{id}/notes` | **403** supervisor only |
| POST | `/serviceman/task/{id}/*-images` | **403** supervisor only |

---

## 7. Request & response examples

All successful API envelopes look like:

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": { },
  "errors": []
}
```

---

### 7.1 Supervisor login

**Request**

```http
POST /api/v1/provider/auth/login
Content-Type: application/json

{
  "email_or_phone": "ops.test@clean365.sa",
  "password": "Clean365Ops@2026",
  "type": "email"
}
```

**Response `200`**

```json
{
  "response_code": "auth_login_200",
  "message": "successfully logged in",
  "content": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "is_active": 1
  },
  "errors": []
}
```

Use `content.token` as `Authorization: Bearer {{supervisor_token}}`.

---

### 7.2 Lean profile — `GET /provider/info`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "role": "supervisor",
    "first_name": "Mohamed",
    "last_name": "Ali",
    "full_name": "Mohamed Ali",
    "email": "ops.test@clean365.sa",
    "phone": "+966500000999",
    "profile_image_full_path": "https://api.clean365.sa/storage/app/public/provider/profile/supervisor.png",
    "zone": {
      "id": "a1614dbe-4732-11ee-9702-dee6e8d77be4",
      "name": "Riyadh North"
    },
    "is_active": 1,
    "is_approved": 1
  },
  "errors": []
}
```

---

### 7.3 Update profile — `PUT /provider/update/profile`

**Request** (multipart form-data)

| Field | Example |
|-------|---------|
| `first_name` | Firas |
| `last_name` | Ahmed |
| `phone` | +966551234567 |
| `email` | ops.test@clean365.sa |
| `profile_image` | (optional file) |

**Response `200`** — same lean profile shape as §7.2 in `content`.

---

### 7.4 Ops dashboard — `GET /provider/ops/dashboard`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "today_summary": {
      "total": 12,
      "completed": 3,
      "upcoming": 5,
      "in_progress": 4,
      "progress_percent": 25,
      "date": "2026-06-27"
    },
    "teams_preview": [
      {
        "id": "b2c3d4e5-f6a7-8901-bcde-f12345678901",
        "name": "Team 1",
        "is_active": true,
        "status": "on_the_way",
        "technicians_count": 3,
        "members": [
          { "id": "c3d4e5f6-a7b8-9012-cdef-123456789012", "name": "Sami Ali", "phone": "+966500000001" }
        ],
        "current_job": {
          "id": "f6a7b8c9-d0e1-2345-f012-456789012345",
          "readable_id": "CLN-2458",
          "title": "Deep Cleaning",
          "field_status": "on_the_way",
          "progress_percent": 10
        }
      }
    ],
    "active_jobs": [
      {
        "id": "f6a7b8c9-d0e1-2345-f012-456789012345",
        "readable_id": "CLN-2458",
        "title": "Deep Cleaning",
        "field_status": "in_progress",
        "booking_status": "ongoing",
        "package_label": "Monthly package",
        "progress_percent": 65,
        "tasks_summary": { "completed": 2, "total": 3 },
        "attendance": { "attended": 3, "total": 3 },
        "schedule_at": "2026-06-27 16:00:00",
        "customer": { "name": "Abdulrahman Jamil", "phone": "+966551234567" },
        "location_text": "An Narjis District, Riyadh",
        "team": { "id": "b2c3d4e5-f6a7-8901-bcde-f12345678901", "name": "Team 2", "members_count": 3 }
      }
    ],
    "upcoming_jobs": []
  },
  "errors": []
}
```

---

### 7.5 Ops teams — `GET /provider/ops/teams`

**Query:** `limit`, `offset`, optional `status`, `search`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "summary": {
      "total": 6,
      "available": 2,
      "in_progress": 3,
      "on_the_way": 1,
      "unavailable": 0
    },
    "teams": {
      "data": [
        {
          "id": "b2c3d4e5-f6a7-8901-bcde-f12345678901",
          "name": "Team 1",
          "is_active": true,
          "status": "in_progress",
          "technicians_count": 3,
          "members": [],
          "current_job": { "id": "f6a7b8c9-d0e1-2345-f012-456789012345", "field_status": "in_progress" }
        }
      ],
      "total": 3,
      "limit": 10,
      "offset": 1
    }
  },
  "errors": []
}
```

---

### 7.6 Ops jobs list — `GET /provider/ops/jobs`

**Query:** `limit`, `offset`, optional `field_status`, `search`, `date`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "summary": {
      "total": 16,
      "progress_percent": 36,
      "assigned": 3,
      "on_the_way": 2,
      "arrived": 1,
      "in_progress": 4,
      "completed": 6
    },
    "jobs": {
      "current_page": 1,
      "data": [
        {
          "id": "f6a7b8c9-d0e1-2345-f012-456789012345",
          "readable_id": "CLN-2458",
          "title": "Deep Cleaning",
          "field_status": "in_progress",
          "booking_status": "ongoing",
          "package_label": "Monthly package",
          "progress_percent": 65,
          "tasks_summary": { "completed": 2, "total": 3 },
          "attendance": { "attended": 3, "total": 3 },
          "schedule_at": "2026-06-27 16:00:00",
          "customer": { "name": "Abdulrahman Jamil", "phone": "+966551234567" },
          "property_type": "Apartment",
          "location_text": "An Narjis District, Riyadh",
          "coordinates": { "lat": 24.8301, "lng": 46.7056 },
          "team": { "id": "b2c3d4e5-f6a7-8901-bcde-f12345678901", "name": "Team 2", "members_count": 3 }
        }
      ],
      "total": 1
    }
  },
  "errors": []
}
```

---

### 7.7 Ops job detail — `GET /provider/ops/jobs/{booking_id}`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "id": "f6a7b8c9-d0e1-2345-f012-456789012345",
    "readable_id": "CLN-2458",
    "title": "Deep Cleaning",
    "field_status": "in_progress",
    "booking_status": "ongoing",
    "package_label": "Monthly package",
    "progress_percent": 65,
    "field_status_timeline": ["assigned", "on_the_way", "arrived", "in_progress", "completed"],
    "customer_note": "Please focus on cleaning the kitchen and bathrooms.",
    "customer": { "name": "Abdulrahman Jamil", "phone": "+966551234567" },
    "location_text": "An Narjis District, Riyadh",
    "coordinates": { "lat": 24.8301, "lng": 46.7056 },
    "attendance": { "attended": 3, "total": 3 },
    "photos": {
      "before": ["https://…/before1.jpg"],
      "during": ["https://…/during1.jpg"],
      "after": [],
      "counts": { "before": 1, "during": 1, "after": 0 }
    },
    "execution_notes": [
      {
        "id": "note-001",
        "note": "Started kitchen and bathroom cleaning; work is on plan.",
        "user_name": "Mohamed Ali",
        "created_at": "2026-06-27T16:10:00.000000Z"
      }
    ],
    "tasks": [
      { "id": "a7b8c9d0-e1f2-3456-0123-567890123456", "title": "Kitchen deep clean", "status": "completed", "attendees_count": 2 },
      { "id": "a7b8c9d0-e1f2-3456-0123-567890123457", "title": "Bathrooms sanitize", "status": "in_progress", "attendees_count": 2 }
    ],
    "team": {
      "id": "b2c3d4e5-f6a7-8901-bcde-f12345678901",
      "name": "Team 2",
      "members_count": 3,
      "attendance": { "attended": 3, "total": 3 }
    }
  },
  "errors": []
}
```

---

### 7.8 Advance field status

**Request**

```http
PUT /api/v1/provider/ops/jobs/{booking_id}/field-status
Authorization: Bearer {{supervisor_token}}
Content-Type: application/json

{ "field_status": "in_progress" }
```

Allowed values (forward only): `assigned` → `on_the_way` → `arrived` → `in_progress` → `completed`.

**Response `200`** — updated job detail (same shape as §7.7). Sync: `in_progress` → `booking_status=ongoing`; `completed` → `booking_status=completed`.

**Response `400` (invalid transition)**

```json
{
  "response_code": "default_400",
  "message": "invalid or missing information",
  "content": null,
  "errors": [
    {
      "error_code": "invalid_field_status_transition",
      "message": "…"
    }
  ]
}
```

---

### 7.9 Manual progress — `PUT /provider/ops/jobs/{id}/progress`

**Request**

```json
{ "progress_percent": 70 }
```

**Response `200`** — job card with updated `progress_percent`.

**Response `400`** when BookingTasks exist (progress is derived):

```json
{
  "response_code": "default_400",
  "message": "invalid or missing information",
  "content": null,
  "errors": [
    {
      "error_code": "progress_percent",
      "message": "Progress is derived from booking tasks and cannot be set manually"
    }
  ]
}
```

---

### 7.10 Execution note — `POST /provider/ops/jobs/{id}/notes`

**Request**

```json
{ "note": "Kitchen and bathrooms started; work is on plan." }
```

**Response `200`**

```json
{
  "response_code": "default_store_200",
  "message": "successfully added",
  "content": {
    "id": "note-002",
    "note": "Kitchen area completed.",
    "user_id": "…",
    "user_name": "Mohamed Ali",
    "created_at": "2026-06-27T16:20:00.000000Z"
  },
  "errors": []
}
```

---

### 7.11 Attendance — `GET /provider/ops/jobs/{id}/attendance`

**Response `200`**

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "summary": { "attended": 3, "total": 3 },
    "members": [
      { "id": "c3d4e5f6-a7b8-9012-cdef-123456789012", "name": "Sami Ali", "is_attended": true, "checked_in_at": "2026-06-27T16:05:00.000000Z" },
      { "id": "…", "name": "Khaled Ahmed", "is_attended": true, "checked_in_at": "2026-06-27T16:06:00.000000Z" }
    ]
  },
  "errors": []
}
```

---

### 7.12 Support / issue — `POST /provider/ops/jobs/{id}/support`

**Request** (multipart)

```
type=operational_issue
message=Water shutoff at property — delayed start
```

`type`: `support` | `operational_issue` | `attachment` (+ optional `attachments[]` files).

**Response `200`**

```json
{
  "response_code": "default_store_200",
  "message": "successfully added",
  "content": {
    "id": "support-001",
    "type": "operational_issue",
    "message": "Water shutoff at property — delayed start",
    "status": "open",
    "created_at": "2026-06-27T16:30:00.000000Z"
  },
  "errors": []
}
```

---

### 7.13 Assign team — `PUT /provider/team/assign-booking/{booking_id}`

**Request**

```json
{ "team_id": "{{team_id}}" }
```

**Response `200`** — lean booking with `team_id` set and `field_status: "assigned"`.

---

### 7.14 Servicemen (view only)

**List** `GET /provider/serviceman/?limit=10&offset=1&status=all`

**Response `200`** (lean rows)

```json
{
  "response_code": "default_200",
  "message": "successfully data fetched",
  "content": {
    "current_page": 1,
    "data": [
      {
        "id": "d4e5f6a7-b8c9-0123-def0-234567890123",
        "first_name": "Ahmed",
        "last_name": "Hassan",
        "full_name": "Ahmed Hassan",
        "email": "ahmed.hassan@clean365.sa",
        "phone": "+966500000001",
        "profile_image_full_path": "https://…",
        "is_active": 1,
        "serviceman_id": "c3d4e5f6-a7b8-9012-cdef-123456789012",
        "user_id": "d4e5f6a7-b8c9-0123-def0-234567890123",
        "provider_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "user_type": "provider-serviceman"
      }
    ],
    "total": 1
  },
  "errors": []
}
```

**Create/update/delete (supervisor) → `403`**

```json
{
  "response_code": "serviceman_admin_only_403",
  "message": "Servicemen can only be created, updated, or deleted by admin",
  "content": null,
  "errors": []
}
```

---

### 7.15 Serviceman check-in — `POST /serviceman/task/{task_id}/check-in`

**Request**

```json
{
  "latitude": "24.8301",
  "longitude": "46.7056"
}
```

**Response `200`**

```json
{
  "response_code": "task_check_in_success_200",
  "message": "Attendance recorded successfully",
  "content": {
    "is_attended": true,
    "attended_at": "2026-06-27T16:05:00.000000Z",
    "check_in_latitude": 24.8301,
    "check_in_longitude": 46.7056
  },
  "errors": []
}
```

**Response `403` (too far)**

```json
{
  "response_code": "task_check_in_too_far_403",
  "message": "You must be within 1 km of the booking location to check in",
  "content": {
    "distance_km": 2.412,
    "allowed_radius_km": 1
  },
  "errors": []
}
```

---

## 8. Postman how-to

1. Import both JSON files from `docs/postman/`.
2. Select environment **Clean365 — Supervisor (Local/Production)**.
3. Set `base_url`, `supervisor_email`, `supervisor_password`, and a real `serviceman_id` if creating teams.
4. Run folder **10 — End-to-End Workflow** in order, or:
   - `01 — Authentication → Supervisor Login`
   - then any folder **02–09**.

Regenerate after API changes:

```bash
python3 docs/postman/generate_collection.py
```

---

## 9. Config flags (supervisor mode)

| Config | Effect |
|--------|--------|
| `SUPERVISOR_MODE=true` | Internal supervisor behavior (no marketplace gates) |
| `SUPERVISOR_CHECK_IN_RADIUS_KM` | Check-in radius (default 1) |

Pending bookings for supervisors: zone + subscribed services (employees, not marketplace cash/verify gates).
