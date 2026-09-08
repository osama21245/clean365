#!/usr/bin/env python3
"""Generate Clean365 Supervisor + Serviceman Postman collections."""
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
from response_fixtures import R  # noqa: E402

BASE = "{{base_url}}/api/v1"
OUT_DIR = Path(__file__).resolve().parent


def hdr_json():
    return [
        {"key": "Accept", "value": "application/json"},
        {"key": "Content-Type", "value": "application/json"},
    ]


def hdr_auth(token_var="supervisor_token"):
    return [
        {"key": "Accept", "value": "application/json"},
        {"key": "Authorization", "value": f"Bearer {{{{{token_var}}}}}"},
    ]


def hdr_form_auth(token_var="supervisor_token"):
    return [
        {"key": "Accept", "value": "application/json"},
        {"key": "Authorization", "value": f"Bearer {{{{{token_var}}}}}"},
    ]


def url(path, query=None):
    parts = path.strip("/").split("/")
    u = {"raw": BASE + path, "host": ["{{base_url}}"], "path": ["api", "v1"] + parts}
    if query:
        u["query"] = [{"key": k, "value": v, "description": d} for k, v, d in query]
    return u


def body_raw(obj):
    return {"mode": "raw", "raw": json.dumps(obj, indent=2), "options": {"raw": {"language": "json"}}}


def body_form(fields):
    return {
        "mode": "formdata",
        "formdata": [{"key": k, "value": v, "type": t, "description": d} for k, v, t, d in fields],
    }


def response(name, code, body_obj, req_method="GET", req_path="/"):
    return {
        "name": name,
        "originalRequest": {"method": req_method, "header": hdr_json(), "url": url(req_path)},
        "status": "OK" if code < 400 else ("Forbidden" if code == 403 else "Not Found" if code == 404 else "Bad Request"),
        "code": code,
        "_postman_previewlanguage": "json",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": json.dumps(body_obj, indent=2),
    }


def req(name, method, path, description, headers=None, body=None, query=None, tests=None, responses=None, auth_token=None):
    if isinstance(headers, dict) and headers.get("mode") in ("raw", "formdata"):
        body = headers
        headers = None
    h = list(headers) if headers is not None else hdr_auth(auth_token or "supervisor_token")
    if body and body.get("mode") == "raw":
        keys = {x.get("key") for x in h if isinstance(x, dict)}
        if "Content-Type" not in keys:
            h = h + [{"key": "Content-Type", "value": "application/json"}]
    item = {
        "name": name,
        "request": {
            "method": method,
            "header": h,
            "url": url(path, query),
            "description": description,
        },
    }
    if body:
        item["request"]["body"] = body
    if tests:
        item["event"] = [{"listen": "test", "script": {"type": "text/javascript", "exec": tests}}]
    if responses:
        item["response"] = responses
    return item


def prerequest_event():
    return [{
        "listen": "prerequest",
        "script": {
            "type": "text/javascript",
            "exec": [
                "if (!pm.request.headers.has('Accept')) {",
                "    pm.request.headers.add({ key: 'Accept', value: 'application/json' });",
                "}",
            ],
        },
    }]


def build_supervisor():
    collection = {
        "info": {
            "_postman_id": "clean365-supervisor-mobile-v4",
            "name": "Clean365 — Supervisor App",
            "description": (
                "# Clean365 Supervisor App API\n\n"
                "Folders match the mobile tabs:\n"
                "1. Auth\n"
                "2. Home (ops dashboard)\n"
                "3. Teams\n"
                "4. Bookings / assigned jobs\n"
                "5. Profile (+ live GPS for admin Live Map)\n\n"
                "A **job** is a booking with `team_id` (no BookingTask / tasks[]).\n"
                "Mark new assignments with `PUT .../jobs/{id}/seen` (`is_seen`, `supervisor_seen_at`).\n"
                "Progress % auto-maps from `field_status` (assigned=0, on_the_way=20, arrived=40, "
                "in_progress=70, completed=100); `PUT .../progress` allows manual override.\n"
                "Attendance: supervisor marks each team member `is_attended` on the booking.\n"
                "Client location includes `lat` + `lng` in `location` and `coordinates`.\n"
                "Live map: call `PUT /provider/update-location` every 5–10s on active jobs / 2–3 min heartbeat.\n"
                "Admin-only actions (create team, accept booking, assign) are **not** in this collection.\n"
            ),
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        },
        "variable": [
            {"key": "base_url", "value": "https://api.clean365.sa", "type": "string"},
            {"key": "supervisor_token", "value": "", "type": "string"},
            {"key": "supervisor_email", "value": "v_j@hotmail.com", "type": "string"},
            {"key": "supervisor_password", "value": "password123", "type": "string"},
            {"key": "team_id", "value": "", "type": "string"},
            {"key": "booking_id", "value": "", "type": "string"},
            {"key": "serviceman_id", "value": "", "type": "string"},
            {"key": "fcm_token", "value": "SAMPLE_FCM_TOKEN", "type": "string"},
            {"key": "lat", "value": "24.7136", "type": "string"},
            {"key": "lng", "value": "46.6753", "type": "string"},
        ],
        "item": [],
        "event": prerequest_event(),
    }

    auth_folder = {
        "name": "01 — Auth",
        "description": "Login first. Token is saved to `supervisor_token`.",
        "item": [
            req(
                "Supervisor Login",
                "POST", "/provider/auth/login",
                "Body: `email_or_phone`, `password`, `type`.\nSaves `supervisor_token`.",
                hdr_json(),
                body_raw({
                    "email_or_phone": "{{supervisor_email}}",
                    "password": "{{supervisor_password}}",
                    "type": "email",
                }),
                tests=[
                    "pm.test('Status 200', () => pm.response.to.have.status(200));",
                    "const j = pm.response.json();",
                    "if (j.content?.token) pm.collectionVariables.set('supervisor_token', j.content.token);",
                ],
                responses=[
                    response("200 Login Success", 200, R["login_supervisor_ok"], "POST", "/provider/auth/login"),
                    response("401 Wrong Password", 401, R["login_401"], "POST", "/provider/auth/login"),
                    response("404 User Not Found", 404, R["login_404"], "POST", "/provider/auth/login"),
                ],
                auth_token=None,
            ),
            req(
                "Supervisor Logout",
                "POST",
                "/user/logout",
                "Revoke supervisor token.",
                responses=[response("200 Logout", 200, R["logout_ok"], "POST", "/user/logout")],
            ),
        ],
    }

    home_folder = {
        "name": "02 — Home",
        "description": "Home tab: today summary, team preview, active/upcoming jobs.\n\n`GET /provider/ops/dashboard`",
        "item": [
            req(
                "Home Dashboard",
                "GET",
                "/provider/ops/dashboard",
                "Today cards + teams_preview + active_jobs + upcoming_jobs.\n"
                "Each job includes `location.text`, `location.lat`, `location.lng`, `progress_percent`, `attendance`.",
                responses=[response("200", 200, R["ops_dashboard"], "GET", "/provider/ops/dashboard")],
            ),
            req(
                "Home Notifications",
                "GET",
                "/provider/notifications",
                "Bell list.\n**Query:** `limit`, `offset`.",
                query=[("limit", "20", "Required"), ("offset", "1", "Required")],
                responses=[response("200", 200, R["provider_notifications"], "GET", "/provider/notifications")],
            ),
        ],
    }

    teams_folder = {
        "name": "03 — Teams",
        "description": (
            "Teams tab (view only — create/assign is admin).\n\n"
            "| Screen | Endpoint |\n|--------|----------|\n"
            "| Summary + list | `GET /provider/ops/teams` |\n"
            "| Team follow-up | `GET /provider/ops/teams/{id}` |\n"
            "| Team CRUD view | `GET /provider/team/` |\n"
            "| Members | `GET /provider/serviceman/` |"
        ),
        "item": [
            req(
                "Teams List + Summary",
                "GET",
                "/provider/ops/teams",
                "Query: `limit`, `offset`, `status` (`all|available|on_the_way|in_progress|unavailable`), `search`.",
                query=[
                    ("limit", "20", "Required"),
                    ("offset", "1", "Required"),
                    ("status", "all", "all|available|on_the_way|in_progress|unavailable"),
                    ("search", "", "Team or member name"),
                ],
                tests=[
                    "const j=pm.response.json();",
                    "const t=j.content?.teams?.data?.[0]||j.content?.teams?.[0];",
                    "if(t?.id) pm.collectionVariables.set('team_id', t.id);",
                ],
                responses=[response("200", 200, R["ops_teams_list"], "GET", "/provider/ops/teams")],
            ),
            req(
                "Team Detail / Follow-up",
                "GET",
                "/provider/ops/teams/{{team_id}}",
                "Members + current_job with client location lat/lng.",
                responses=[response("200", 200, R["ops_team_show"], "GET", "/provider/ops/teams/{id}")],
            ),
            req(
                "List Teams (setup)",
                "GET",
                "/provider/team/",
                "Lean teams + `assigned_bookings`.",
                query=[("limit", "20", ""), ("offset", "1", ""), ("status", "all", "")],
                responses=[response("200", 200, R["team_list"], "GET", "/provider/team/")],
            ),
            req(
                "Get Team (setup)",
                "GET",
                "/provider/team/{{team_id}}",
                "Team members + assigned bookings.",
                responses=[response("200", 200, R["team_show"], "GET", "/provider/team/{id}")],
            ),
            req(
                "List Servicemen",
                "GET",
                "/provider/serviceman/",
                "View-only member list.",
                query=[("limit", "20", "Required"), ("offset", "1", "Required"), ("status", "all", "")],
                tests=[
                    "const j=pm.response.json();",
                    "const row=j.content?.data?.[0];",
                    "if(row?.serviceman_id) pm.collectionVariables.set('serviceman_id', row.serviceman_id);",
                ],
                responses=[response("200", 200, R["serviceman_list"], "GET", "/provider/serviceman/")],
            ),
            req(
                "Get Serviceman",
                "GET",
                "/provider/serviceman/{{serviceman_id}}",
                "Member profile.",
                responses=[response("200", 200, R["serviceman_show"], "GET", "/provider/serviceman/{id}")],
            ),
        ],
    }

    bookings_folder = {
        "name": "04 — Bookings",
        "description": (
            "Assigned jobs = bookings with `team_id`.\n"
            "Flow: list → detail → **seen** → field-status → attendance / photos / notes / support.\n"
            "No BookingTask endpoints — attendance / photos / notes / field_status live on the booking.\n"
            "Location: `location.{text,lat,lng}` and `coordinates.{lat,lng}`."
        ),
        "item": [
            req(
                "Assigned Jobs List",
                "GET",
                "/provider/ops/jobs",
                "Query: `field_status`, `search`, `date`, `limit`, `offset`.",
                query=[
                    ("limit", "20", "Required"),
                    ("offset", "1", "Required"),
                    ("field_status", "all", "all|assigned|on_the_way|arrived|in_progress|completed"),
                    ("search", "", "Order / customer / team"),
                    ("date", "", "YYYY-MM-DD"),
                ],
                tests=[
                    "const j=pm.response.json();",
                    "const d=j.content?.jobs?.data;",
                    "if(d?.[0]?.id) pm.collectionVariables.set('booking_id', d[0].id);",
                ],
                responses=[response("200", 200, R["ops_jobs_list"], "GET", "/provider/ops/jobs")],
            ),
            req(
                "Job Detail",
                "GET",
                "/provider/ops/jobs/{{booking_id}}",
                "Stepper, progress %, attendance summary, team, customer, location, photos, notes.\n"
                "**No `tasks[]`** — the booking itself is the job.",
                responses=[response("200", 200, R["ops_job_detail"], "GET", "/provider/ops/jobs/{id}")],
            ),
            req(
                "Mark Job Seen",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/seen",
                "Acknowledge a newly assigned booking (idempotent).\n"
                "Sets `supervisor_seen_at` / `is_seen=true`. Admin booking details shows Seen / Not seen yet.\n"
                "Re-assigning a team clears seen.",
                responses=[response("200", 200, R["ops_seen_ok"], "PUT", "/provider/ops/jobs/{id}/seen")],
            ),
            req(
                "Field Status → on_the_way",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/field-status",
                "Auto progress → 20%. Body: `{ \"field_status\": \"on_the_way\" }`",
                body=body_raw({"field_status": "on_the_way"}),
                responses=[response("200", 200, R["ops_field_status_ok"], "PUT", "/provider/ops/jobs/{id}/field-status")],
            ),
            req(
                "Field Status → arrived",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/field-status",
                "Auto progress → 40%.",
                body=body_raw({"field_status": "arrived"}),
                responses=[response("200", 200, R["ops_field_status_ok"], "PUT", "/provider/ops/jobs/{id}/field-status")],
            ),
            req(
                "Field Status → in_progress",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/field-status",
                "Auto progress → 70%. Also sets `booking_status=ongoing`.",
                body=body_raw({"field_status": "in_progress"}),
                responses=[response("200", 200, R["ops_field_status_ok"], "PUT", "/provider/ops/jobs/{id}/field-status")],
            ),
            req(
                "Field Status → completed",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/field-status",
                "Auto progress → 100%. Also sets `booking_status=completed`.",
                body=body_raw({"field_status": "completed"}),
                responses=[response("200", 200, R["ops_field_status_ok"], "PUT", "/provider/ops/jobs/{id}/field-status")],
            ),
            req(
                "Manual Progress Override",
                "PUT",
                "/provider/ops/jobs/{{booking_id}}/progress",
                "Body: `{ \"progress_percent\": 0..100 }` — overrides auto value from field_status.",
                body=body_raw({"progress_percent": 70}),
                responses=[
                    response("200", 200, R["ops_progress_ok"], "PUT", "/provider/ops/jobs/{id}/progress"),
                    response("400", 400, R["ops_progress_blocked"], "PUT", "/provider/ops/jobs/{id}/progress"),
                ],
            ),
            req(
                "Add Execution Note",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/notes",
                "Body: `{ \"note\": \"...\" }`",
                body=body_raw({"note": "Kitchen and bathrooms started; work is on plan."}),
                responses=[response("200", 200, R["ops_note_ok"], "POST", "/provider/ops/jobs/{id}/notes")],
            ),
            req(
                "Get Attendance",
                "GET",
                "/provider/ops/jobs/{{booking_id}}/attendance",
                "Returns `{ summary: {attended,total}, members: [{id,name,phone,is_attended,attended_at}] }`.",
                responses=[response("200", 200, R["ops_attendance"], "GET", "/provider/ops/jobs/{id}/attendance")],
            ),
            req(
                "Register Member Attendance",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/attendance",
                "Supervisor marks a team member present or absent.\n\n"
                "**Body:** `{ \"serviceman_id\": \"...\", \"is_attended\": true|false }`\n"
                "No lat/lng / check-in — supervisor-only mark.",
                body=body_raw({"serviceman_id": "{{serviceman_id}}", "is_attended": True}),
                responses=[response("200", 200, R["ops_attendance"], "POST", "/provider/ops/jobs/{id}/attendance")],
            ),
            req(
                "Upload Before Photos",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/images/before",
                "multipart `images[]`",
                hdr_form_auth(),
                body_form([("images", "", "file", "One or more")]),
                responses=[response("200", 200, R["ops_images_ok"], "POST", "/provider/ops/jobs/{id}/images/before")],
            ),
            req(
                "Upload During Photos",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/images/during",
                "multipart `images[]`",
                hdr_form_auth(),
                body_form([("images", "", "file", "One or more")]),
                responses=[response("200", 200, R["ops_images_ok"], "POST", "/provider/ops/jobs/{id}/images/during")],
            ),
            req(
                "Upload After Photos",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/images/after",
                "multipart `images[]`",
                hdr_form_auth(),
                body_form([("images", "", "file", "One or more")]),
                responses=[response("200", 200, R["ops_images_ok"], "POST", "/provider/ops/jobs/{id}/images/after")],
            ),
            req(
                "Report Operational Issue",
                "POST",
                "/provider/ops/jobs/{{booking_id}}/support",
                "type=`operational_issue` | `support` | `attachment`",
                hdr_form_auth(),
                body_form([
                    ("type", "operational_issue", "text", ""),
                    ("message", "Team needs extra sanitizing tools", "text", ""),
                ]),
                responses=[response("200", 200, R["ops_support_ok"], "POST", "/provider/ops/jobs/{id}/support")],
            ),
            req(
                "List Bookings (pipeline)",
                "POST",
                "/provider/booking/",
                "Use `accepted` / `ongoing` / `completed` / `all`. Pending is admin-only.",
                body_raw({"limit": 10, "offset": 1, "booking_status": "accepted"}),
                tests=[
                    "const j=pm.response.json();",
                    "const d=j.content?.bookings?.data||j.content?.data;",
                    "if(d?.[0]?.id) pm.collectionVariables.set('booking_id', d[0].id);",
                ],
                responses=[response("200", 200, R["booking_list"], "POST", "/provider/booking/")],
            ),
            req(
                "Get Booking Details",
                "GET",
                "/provider/booking/{{booking_id}}",
                "Lean booking detail with location lat/lng.",
                responses=[response("200", 200, R["booking_show"], "GET", "/provider/booking/{id}")],
            ),
        ],
    }

    profile_folder = {
        "name": "05 — Profile",
        "description": (
            "Profile tab: info, edit, password, FCM, **live location**, app config.\n"
            "GPS: `PUT /provider/update-location` powers admin Live Map (not attendance)."
        ),
        "item": [
            req(
                "Get Profile",
                "GET",
                "/provider/info",
                "Lean supervisor profile.",
                responses=[response("200", 200, R["provider_profile"], "GET", "/provider/info")],
            ),
            req(
                "Update Profile",
                "PUT",
                "/provider/update/profile",
                "multipart: first_name, last_name, phone, email, profile_image.",
                hdr_form_auth(),
                body_form([
                    ("first_name", "Abdullah", "text", "Required"),
                    ("last_name", "Khalaf", "text", ""),
                    ("phone", "+966566347445", "text", "Required"),
                    ("email", "v_j@hotmail.com", "text", ""),
                    ("profile_image", "", "file", "Optional"),
                ]),
                responses=[response("200", 200, R["provider_profile_update_ok"], "PUT", "/provider/update/profile")],
            ),
            req(
                "Update Password",
                "PUT",
                "/provider/update/password",
                "Body: current_password, password, confirm_password.",
                body_raw({
                    "current_password": "{{supervisor_password}}",
                    "password": "password123",
                    "confirm_password": "password123",
                }),
                responses=[response("200", 200, R["provider_password_ok"], "PUT", "/provider/update/password")],
            ),
            req(
                "Update FCM Token",
                "PUT",
                "/provider/update/fcm-token",
                "Body: `{ \"fcm_token\": \"...\" }`",
                body_raw({"fcm_token": "{{fcm_token}}"}),
                responses=[response("200", 200, R["provider_fcm_ok"], "PUT", "/provider/update/fcm-token")],
            ),
            req(
                "Update Location (live map)",
                "PUT",
                "/provider/update-location",
                "GPS ping for **admin Live Map** (`/admin/live-map`).\n"
                "- Saves `users.latitude` / `longitude` and always refreshes `last_seen_at`.\n"
                "- Call every **5–10s** while on an active field job; every **2–3 min** as heartbeat.\n"
                "- Rate limit: ~30 writes/min (`429` if exceeded).\n"
                "- **Not** attendance / job check-in.\n"
                "Body: `{ \"latitude\": number, \"longitude\": number }` (both optional for heartbeat-only).",
                body=body_raw({"latitude": "{{lat}}", "longitude": "{{lng}}"}),
                responses=[
                    response("200", 200, R["update_location_ok"], "PUT", "/provider/update-location"),
                ],
            ),
            req(
                "Update Location Heartbeat (no coords)",
                "PUT",
                "/provider/update-location",
                "Presence-only ping — refreshes `last_seen_at` without changing coords.\n"
                "Empty body `{}` is valid.",
                body=body_raw({}),
                responses=[
                    response("200", 200, R["update_location_heartbeat_ok"], "PUT", "/provider/update-location"),
                ],
            ),
            req(
                "App Config",
                "GET",
                "/provider/config",
                "Public config / feature flags.",
                hdr_json(),
                auth_token=None,
                responses=[response("200", 200, R["provider_config"], "GET", "/provider/config")],
            ),
        ],
    }

    errors_folder = {
        "name": "06 — Errors",
        "description": "Common failures for mobile handling.",
        "item": [
            req(
                "401 — Missing Token",
                "GET",
                "/provider/ops/dashboard",
                "No Authorization.",
                [{"key": "Accept", "value": "application/json"}],
                responses=[response("401 Unauthenticated", 401, R["unauthenticated"], "GET", "/provider/ops/dashboard")],
            ),
            req(
                "400 — Teams Missing Params",
                "GET",
                "/provider/ops/teams",
                "Missing limit/offset.",
                query=[],
                responses=[response("400", 400, R["validation_400"], "GET", "/provider/ops/teams")],
            ),
            req(
                "204 — Job Not Found",
                "GET",
                "/provider/ops/jobs/00000000-0000-0000-0000-000000000000",
                "",
                responses=[response("204", 200, R["not_found_204"], "GET", "/provider/ops/jobs/{id}")],
            ),
            req(
                "204 — Team Not Found",
                "GET",
                "/provider/ops/teams/00000000-0000-0000-0000-000000000000",
                "",
                responses=[response("204", 200, R["not_found_204"], "GET", "/provider/ops/teams/{id}")],
            ),
            req(
                "403 — Admin Assignment Only",
                "PUT",
                "/provider/booking/request-accept/{{booking_id}}",
                "Supervisor cannot accept bookings — admin assigns.",
                responses=[response("403", 403, R["admin_assignment_only_403"], "PUT", "/provider/booking/request-accept/{id}")],
            ),
        ],
    }

    collection["item"] = [auth_folder, home_folder, teams_folder, bookings_folder, profile_folder, errors_folder]
    return collection


def build_serviceman():
    sm = "serviceman_token"
    collection = {
        "info": {
            "_postman_id": "clean365-serviceman-mobile-v2",
            "name": "Clean365 — Serviceman App",
            "description": (
                "# Clean365 Serviceman (Field Technician) API\n\n"
                "Serviceman sees **assigned bookings** (direct `serviceman_id` or team membership).\n"
                "- **No** BookingTask, `/provider/booking-task/*`, or attendance write APIs — supervisor marks attendance.\n"
                "- On team jobs (`team_id` set): `can_update_booking_status=false`; "
                "status updates return `booking_status_supervisor_only_403`.\n"
                "- Detail may include `field_status`, `progress_percent`, `attendances` (read-only).\n"
                "- Use GPS via `PUT /serviceman/update-location` for admin Live Map only.\n"
            ),
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        },
        "variable": [
            {"key": "base_url", "value": "https://api.clean365.sa", "type": "string"},
            {"key": "serviceman_token", "value": "", "type": "string"},
            {"key": "serviceman_phone", "value": "+201022181170", "type": "string"},
            {"key": "serviceman_password", "value": "password123", "type": "string"},
            {"key": "booking_id", "value": "", "type": "string"},
            {"key": "fcm_token", "value": "SAMPLE_FCM_TOKEN", "type": "string"},
            {"key": "lat", "value": "24.7136", "type": "string"},
            {"key": "lng", "value": "46.6753", "type": "string"},
        ],
        "item": [],
        "event": prerequest_event(),
    }

    auth_folder = {
        "name": "01 — Auth",
        "item": [
            req(
                "Serviceman Login",
                "POST",
                "/serviceman/auth/login",
                "Body: `phone`, `password`. Saves `serviceman_token`.",
                hdr_json(),
                body_raw({"phone": "{{serviceman_phone}}", "password": "{{serviceman_password}}"}),
                tests=[
                    "pm.test('Status 200', () => pm.response.to.have.status(200));",
                    "const j = pm.response.json();",
                    "if (j.content?.token) pm.collectionVariables.set('serviceman_token', j.content.token);",
                ],
                responses=[
                    response("200 Login Success", 200, R["login_serviceman_ok"], "POST", "/serviceman/auth/login"),
                    response("401 Wrong Password", 401, R["login_401"], "POST", "/serviceman/auth/login"),
                ],
                auth_token=None,
            ),
            req(
                "Forgot Password",
                "POST",
                "/serviceman/forgot-password",
                "Send OTP to phone/email.",
                hdr_json(),
                body_raw({"identity": "{{serviceman_phone}}"}),
                auth_token=None,
            ),
            req(
                "OTP Verification",
                "POST",
                "/serviceman/otp-verification",
                "Verify reset OTP.",
                hdr_json(),
                body_raw({"identity": "{{serviceman_phone}}", "otp": "123456"}),
                auth_token=None,
            ),
            req(
                "Reset Password",
                "PUT",
                "/serviceman/reset-password",
                "After OTP verified.",
                hdr_json(),
                body_raw({
                    "identity": "{{serviceman_phone}}",
                    "otp": "123456",
                    "password": "password123",
                    "confirm_password": "password123",
                }),
                auth_token=None,
            ),
            req(
                "Logout",
                "POST",
                "/user/logout",
                "Revoke token.",
                auth_token=sm,
                responses=[response("200 Logout", 200, R["logout_ok"], "POST", "/user/logout")],
            ),
        ],
    }

    home_folder = {
        "name": "02 — Home & Profile",
        "item": [
            req(
                "Dashboard",
                "GET",
                "/serviceman/dashboard",
                "Query `sections` = comma list: `top_cards,recent_bookings,booking_stats`.",
                auth_token=sm,
                query=[
                    ("sections", "top_cards,recent_bookings", "Required"),
                    ("year", "2026", ""),
                    ("month", "9", ""),
                ],
                responses=[response("200", 200, R["serviceman_dashboard"], "GET", "/serviceman/dashboard")],
            ),
            req(
                "Booking Statistics",
                "GET",
                "/serviceman/dashboard/booking-statistics",
                "Yearly/monthly chart data.",
                auth_token=sm,
                query=[("year", "2026", ""), ("stats_type", "full_year", "full_year|full_month")],
            ),
            req(
                "My Info",
                "GET",
                "/serviceman/info",
                "Current serviceman profile.",
                auth_token=sm,
                responses=[response("200", 200, R["serviceman_info"], "GET", "/serviceman/info")],
            ),
            req(
                "Update Profile",
                "PUT",
                "/serviceman/update/profile",
                "multipart profile fields.",
                hdr_form_auth(sm),
                body_form([
                    ("first_name", "Ahmed", "text", ""),
                    ("last_name", "Hassan", "text", ""),
                    ("phone", "{{serviceman_phone}}", "text", ""),
                    ("profile_image", "", "file", "Optional"),
                ]),
            ),
            req(
                "Update Profile Info",
                "PUT",
                "/serviceman/profile/info",
                "Profile info update.",
                auth_token=sm,
                body=body_raw({"first_name": "Ahmed", "last_name": "Hassan"}),
            ),
            req(
                "Change Password",
                "PUT",
                "/serviceman/profile/change-password",
                "Body: current_password, password, confirm_password.",
                auth_token=sm,
                body=body_raw({
                    "current_password": "{{serviceman_password}}",
                    "password": "password123",
                    "confirm_password": "password123",
                }),
            ),
            req(
                "Update FCM Token",
                "PUT",
                "/serviceman/update/fcm-token",
                "Body: `{ \"fcm_token\": \"...\" }`",
                auth_token=sm,
                body=body_raw({"fcm_token": "{{fcm_token}}"}),
                responses=[response("200", 200, R["provider_fcm_ok"], "PUT", "/serviceman/update/fcm-token")],
            ),
            req(
                "Update Location (live map)",
                "PUT",
                "/serviceman/update-location",
                "GPS ping for **admin Live Map** (`/admin/live-map`).\n"
                "- Saves `users.latitude` / `longitude` and always refreshes `last_seen_at`.\n"
                "- Call every **5–10s** while on an active field job; every **2–3 min** as heartbeat.\n"
                "- Rate limit: ~30 writes/min (`429` if exceeded).\n"
                "- **Not** attendance check-in (supervisor marks attendance).\n"
                "Body: `{ \"latitude\": number, \"longitude\": number }` (both optional for heartbeat-only).",
                auth_token=sm,
                body=body_raw({"latitude": "{{lat}}", "longitude": "{{lng}}"}),
                responses=[
                    response("200", 200, R["update_location_ok"], "PUT", "/serviceman/update-location"),
                ],
            ),
            req(
                "Update Location Heartbeat (no coords)",
                "PUT",
                "/serviceman/update-location",
                "Presence-only ping — refreshes `last_seen_at` without changing coords.\n"
                "Empty body `{}` is valid.",
                auth_token=sm,
                body=body_raw({}),
                responses=[
                    response("200", 200, R["update_location_heartbeat_ok"], "PUT", "/serviceman/update-location"),
                ],
            ),
            req(
                "Change Language",
                "POST",
                "/serviceman/change-language",
                "Body: `{ \"lang\": \"ar\"|\"en\" }`",
                hdr_json(),
                body_raw({"lang": "ar"}),
                auth_token=None,
            ),
            req(
                "App Config",
                "GET",
                "/serviceman/config/",
                "Public config (no auth required).",
                hdr_json(),
                auth_token=None,
            ),
        ],
    }

    bookings_folder = {
        "name": "03 — Bookings",
        "description": (
            "Serviceman booking list/detail.\n"
            "Team bookings: read-only status (`can_update_booking_status=false`); "
            "may include `field_status`, `progress_percent`, `attendances`.\n"
            "No task / attendance write APIs — supervisor owns field ops."
        ),
        "item": [
            req(
                "List My Bookings",
                "GET",
                "/serviceman/booking/list",
                "Query: booking_status, limit, offset.",
                auth_token=sm,
                query=[
                    ("limit", "20", "Required"),
                    ("offset", "1", "Required"),
                    ("booking_status", "all", "pending|accepted|ongoing|completed|canceled|all"),
                ],
                tests=[
                    "const j=pm.response.json();",
                    "const d=j.content?.data||j.content?.bookings?.data;",
                    "if(d?.[0]?.id) pm.collectionVariables.set('booking_id', d[0].id);",
                ],
                responses=[response("200", 200, R["serviceman_booking_list"], "GET", "/serviceman/booking/list")],
            ),
            req(
                "Booking Detail (team)",
                "GET",
                "/serviceman/booking/detail/{{booking_id}}",
                "Team order — `can_update_booking_status=false`. Includes team + attendances (read-only).",
                auth_token=sm,
                responses=[
                    response("200 Team", 200, R["serviceman_booking_detail_team"], "GET", "/serviceman/booking/detail/{id}"),
                ],
            ),
            req(
                "Booking Detail (solo)",
                "GET",
                "/serviceman/booking/detail/{{booking_id}}",
                "Solo assignment — status update may be allowed.",
                auth_token=sm,
                responses=[
                    response("200 Solo", 200, R["serviceman_booking_detail_solo"], "GET", "/serviceman/booking/detail/{id}"),
                ],
            ),
            req(
                "Update Booking Status",
                "PUT",
                "/serviceman/booking/status-update/{{booking_id}}",
                "Solo jobs only. Team jobs return `booking_status_supervisor_only_403`.",
                auth_token=sm,
                body=body_raw({"booking_status": "ongoing"}),
                responses=[
                    response("200", 200, R["booking_status_ok"], "PUT", "/serviceman/booking/status-update/{id}"),
                    response("403 Team", 403, R["booking_supervisor_only"], "PUT", "/serviceman/booking/status-update/{id}"),
                ],
            ),
            req(
                "Single Repeat Status Update",
                "PUT",
                "/serviceman/booking/single-repeat-status-update/{{booking_id}}",
                "Repeat booking instance status.",
                auth_token=sm,
                body=body_raw({"booking_status": "completed"}),
            ),
            req(
                "Payment Status Update",
                "PUT",
                "/serviceman/booking/payment-status-update/{{booking_id}}",
                "Mark payment collected when allowed.",
                auth_token=sm,
                body=body_raw({"payment_status": "paid"}),
            ),
            req(
                "Get Service Info",
                "GET",
                "/serviceman/booking/service/info",
                "Service variants for edit flows.",
                auth_token=sm,
                query=[("service_id", "", ""), ("zone_id", "", "")],
            ),
        ],
    }

    notif_folder = {
        "name": "04 — Notifications",
        "item": [
            req(
                "Push Notifications",
                "GET",
                "/serviceman/push-notifications",
                "Push notification list.",
                auth_token=sm,
                query=[("limit", "20", ""), ("offset", "1", "")],
            ),
            req(
                "Inbox Notifications",
                "GET",
                "/serviceman/inbox-notifications",
                "In-app inbox.",
                auth_token=sm,
                query=[("limit", "20", ""), ("offset", "1", "")],
            ),
            req(
                "Inbox Notification Detail",
                "GET",
                "/serviceman/inbox-notifications/{{booking_id}}",
                "Replace id with notification id.",
                auth_token=sm,
            ),
            req(
                "Ack Broadcast",
                "POST",
                "/serviceman/broadcasts/ack",
                "Acknowledge ad/broadcast.",
                auth_token=sm,
                body=body_raw({"broadcast_id": ""}),
            ),
        ],
    }

    errors_folder = {
        "name": "05 — Errors",
        "item": [
            req(
                "401 — Missing Token",
                "GET",
                "/serviceman/info",
                "No Authorization.",
                [{"key": "Accept", "value": "application/json"}],
                responses=[response("401", 401, R["unauthenticated"], "GET", "/serviceman/info")],
            ),
            req(
                "403 — Team Status Supervisor Only",
                "PUT",
                "/serviceman/booking/status-update/{{booking_id}}",
                "Team-assigned booking — only supervisor updates status.",
                auth_token=sm,
                body=body_raw({"booking_status": "ongoing"}),
                responses=[response("403", 403, R["booking_supervisor_only"], "PUT", "/serviceman/booking/status-update/{id}")],
            ),
        ],
    }

    collection["item"] = [auth_folder, home_folder, bookings_folder, notif_folder, errors_folder]
    return collection


def write_json(path: Path, data: dict):
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    print(f"Generated: {path}")


supervisor = build_supervisor()
serviceman = build_serviceman()

supervisor_env = {
    "id": "clean365-supervisor-local",
    "name": "Clean365 — Supervisor (Local/Production)",
    "values": [
        {"key": "base_url", "value": "https://api.clean365.sa", "type": "default", "enabled": True},
        {"key": "supervisor_email", "value": "v_j@hotmail.com", "type": "default", "enabled": True},
        {"key": "supervisor_password", "value": "password123", "type": "secret", "enabled": True},
        {"key": "supervisor_token", "value": "", "type": "secret", "enabled": True},
        {"key": "team_id", "value": "bdc8afda-fd24-4f3d-9723-6d68045118ec", "type": "default", "enabled": True},
        {"key": "booking_id", "value": "", "type": "default", "enabled": True},
        {"key": "serviceman_id", "value": "40669922-c1f1-47fa-9089-4aba7df0ed32", "type": "default", "enabled": True},
        {"key": "fcm_token", "value": "SAMPLE_FCM_TOKEN", "type": "default", "enabled": True},
        {"key": "lat", "value": "24.7136", "type": "default", "enabled": True},
        {"key": "lng", "value": "46.6753", "type": "default", "enabled": True},
    ],
    "_postman_variable_scope": "environment",
}

serviceman_env = {
    "id": "clean365-serviceman-local",
    "name": "Clean365 — Serviceman (Local/Production)",
    "values": [
        {"key": "base_url", "value": "https://api.clean365.sa", "type": "default", "enabled": True},
        {"key": "serviceman_phone", "value": "+201022181170", "type": "default", "enabled": True},
        {"key": "serviceman_password", "value": "password123", "type": "secret", "enabled": True},
        {"key": "serviceman_token", "value": "", "type": "secret", "enabled": True},
        {"key": "booking_id", "value": "", "type": "default", "enabled": True},
        {"key": "fcm_token", "value": "SAMPLE_FCM_TOKEN", "type": "default", "enabled": True},
        {"key": "lat", "value": "24.7136", "type": "default", "enabled": True},
        {"key": "lng", "value": "46.6753", "type": "default", "enabled": True},
    ],
    "_postman_variable_scope": "environment",
}

write_json(OUT_DIR / "Clean365-Supervisor-API.postman_collection.json", supervisor)
write_json(OUT_DIR / "Clean365-Supervisor-Local.postman_environment.json", supervisor_env)
write_json(OUT_DIR / "Clean365-Serviceman-API.postman_collection.json", serviceman)
write_json(OUT_DIR / "Clean365-Serviceman-Local.postman_environment.json", serviceman_env)

print("Supervisor folders:")
for folder in supervisor["item"]:
    print(f"  - {folder['name']}: {len(folder['item'])} requests")
print("Serviceman folders:")
for folder in serviceman["item"]:
    print(f"  - {folder['name']}: {len(folder['item'])} requests")
