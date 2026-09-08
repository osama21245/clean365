#!/usr/bin/env python3
"""Realistic API response fixtures for Clean365 Supervisor Postman collection."""

import json

# ── Shared UUIDs (use as Postman variables where dynamic) ──────────────────
IDS = {
    "provider_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "supervisor_user_id": "b2c3d4e5-f6a7-8901-bcde-f12345678901",
    "serviceman_id": "c3d4e5f6-a7b8-9012-cdef-123456789012",
    "serviceman_user_id": "d4e5f6a7-b8c9-0123-def0-234567890123",
    "team_id": "e5f6a7b8-c9d0-1234-ef01-345678901234",
    "booking_id": "f6a7b8c9-d0e1-2345-f012-456789012345",
    "task_id": "a7b8c9d0-e1f2-3456-0123-567890123456",
    "attendee_id": "b8c9d0e1-f2a3-4567-1234-678901234567",
    "zone_id": "c9d0e1f2-a3b4-5678-2345-789012345678",
    "customer_id": "d0e1f2a3-b4c5-6789-3456-890123456789",
    "category_id": "e1f2a3b4-c5d6-7890-4567-901234567890",
    "sub_category_id": "f2a3b4c5-d6e7-8901-5678-012345678901",
}

TS = "2026-07-11T14:30:00.000000Z"
IMG = "https://clean365.tech/storage/app/public/serviceman/profile/2026-07-11-abc123.png"
TASK_IMG = "https://clean365.tech/storage/app/public/booking/task/2026-07-11-task001.png"


def envelope(code, message, content=None, errors=None):
    return {
        "response_code": code,
        "message": message,
        "content": content,
        "errors": errors or [],
    }


def paginate(items, path="/api/v1/provider/serviceman", per_page=10):
    total = len(items)
    return {
        "current_page": 1,
        "data": items,
        "first_page_url": f"{path}?offset=1",
        "from": 1 if items else None,
        "last_page": 1,
        "last_page_url": f"{path}?offset=1",
        "links": [
            {"url": None, "label": "&laquo; Previous", "active": False},
            {"url": f"{path}?offset=1", "label": "1", "active": True},
            {"url": None, "label": "Next &raquo;", "active": False},
        ],
        "next_page_url": None,
        "path": path,
        "per_page": per_page,
        "prev_page_url": None,
        "to": total if items else None,
        "total": total,
    }


def user_serviceman():
    return {
        "id": IDS["serviceman_user_id"],
        "first_name": "Ahmed",
        "last_name": "Hassan",
        "email": "ahmed.hassan@clean365.tech",
        "phone": "+201000000001",
        "profile_image": "serviceman/profile/2026-07-11-abc123.png",
        "profile_image_full_path": IMG,
        "identification_number": "29801011234567",
        "identification_type": "nid",
        "identification_image": [
            {"image": "serviceman/identity/id-front.png", "storage": "public"},
            {"image": "serviceman/identity/id-back.png", "storage": "public"},
        ],
        "identification_image_full_path": [
            "https://clean365.tech/storage/app/public/serviceman/identity/id-front.png",
            "https://clean365.tech/storage/app/public/serviceman/identity/id-back.png",
        ],
        "user_type": "provider-serviceman",
        "is_active": 1,
        "is_phone_verified": 1,
        "is_email_verified": 1,
        "created_at": TS,
        "updated_at": TS,
        "serviceman": {
            "id": IDS["serviceman_id"],
            "user_id": IDS["serviceman_user_id"],
            "provider_id": IDS["provider_id"],
            "created_at": TS,
            "updated_at": TS,
        },
    }


def user_supervisor_owner():
    return {
        "id": IDS["supervisor_user_id"],
        "first_name": "Mohamed",
        "last_name": "Ali",
        "email": "supervisor@clean365.tech",
        "phone": "+201000000099",
        "profile_image": "provider/profile/supervisor.png",
        "profile_image_full_path": "https://clean365.tech/storage/app/public/provider/profile/supervisor.png",
        "user_type": "provider-admin",
        "is_active": 1,
        "is_phone_verified": 1,
        "is_email_verified": 1,
        "created_at": TS,
        "updated_at": TS,
    }


def serviceman_record(with_user=True):
    # Kept for nested legacy references; prefer lean_serviceman() in examples.
    base = {
        "id": IDS["serviceman_id"],
        "user_id": IDS["serviceman_user_id"],
        "provider_id": IDS["provider_id"],
        "created_at": TS,
        "updated_at": TS,
    }
    if with_user:
        base["user"] = {
            "id": IDS["serviceman_user_id"],
            "first_name": "Ahmed",
            "last_name": "Hassan",
            "email": "ahmed.hassan@clean365.tech",
            "phone": "+201000000001",
            "profile_image_full_path": IMG,
            "is_active": 1,
        }
    return base


def lean_serviceman():
    return {
        "id": IDS["serviceman_user_id"],
        "first_name": "Ahmed",
        "last_name": "Hassan",
        "full_name": "Ahmed Hassan",
        "email": "ahmed.hassan@clean365.tech",
        "phone": "+201000000001",
        "profile_image_full_path": IMG,
        "is_active": 1,
        "serviceman_id": IDS["serviceman_id"],
        "user_id": IDS["serviceman_user_id"],
        "provider_id": IDS["provider_id"],
        "user_type": "provider-serviceman",
    }


def serviceman_show():
    rec = lean_serviceman()
    rec["bookings_count"] = {"ongoing": 2, "completed": 15, "canceled": 1}
    return rec


def team(with_members=True):
    t = {
        "id": IDS["team_id"],
        "name": "Morning Cleaning Crew",
        "is_active": True,
        "members_count": 2 if with_members else 0,
    }
    if with_members:
        t["members"] = [
            {
                "id": IDS["serviceman_id"],
                "name": "Ahmed Hassan",
                "phone": "+201000000001",
                "profile_image": IMG,
                "is_active": 1,
            },
            {
                "id": "d5e6f7a8-b9c0-1234-def0-345678901234",
                "name": "Sara Mahmoud",
                "phone": "+201000000002",
                "profile_image": IMG,
                "is_active": 1,
            },
        ]
    return t


def task_attendee(attended=False):
    return {
        "id": IDS["attendee_id"],
        "serviceman_id": IDS["serviceman_id"],
        "name": "Ahmed Hassan",
        "phone": "+201000000001",
        "is_attended": attended,
        "attended_at": TS if attended else None,
        "notes": "Started kitchen area, grease buildup behind stove." if attended else None,
    }


def booking_task(status="pending", with_attendees=True):
    t = {
        "id": IDS["task_id"],
        "booking_id": IDS["booking_id"],
        "team_id": IDS["team_id"],
        "title": "Deep clean kitchen",
        "description": "Full kitchen sanitization including appliances and cabinets.",
        "status": status,
        "supervisor_notes": "Bring eco-friendly supplies. Customer allergic to strong chemicals.",
        "before_images_full_path": [TASK_IMG],
        "after_images_full_path": [TASK_IMG.replace("task001", "after001")] if status == "completed" else [],
        "team": {"id": IDS["team_id"], "name": "Morning Cleaning Crew"},
        "created_at": TS,
    }
    if with_attendees:
        t["attendees"] = [task_attendee(attended=status != "pending")]
    return t


def booking(status="accepted", with_team=True):
    # Lean supervisor booking detail/list shape.
    b = {
        "id": IDS["booking_id"],
        "readable_id": 100042,
        "booking_status": status,
        "field_status": "assigned" if with_team else None,
        "progress_percent": 65 if status == "ongoing" else 0,
        "service_schedule": "2026-07-12 09:00:00",
        "title": "Deep Cleaning",
        "customer": {
            "id": IDS["customer_id"],
            "name": "Fatma Ibrahim",
            "phone": "+201012345678",
        },
        "property_type": "Apartment",
        "location_text": "15 Nile Corniche, Zamalek, Cairo",
        "team": {"id": IDS["team_id"], "name": "Morning Cleaning Crew"} if with_team else None,
        "is_paid": 1,
        "total_booking_amount": 450.0,
        "customer_note": "Please focus on kitchen and bathrooms.",
        "coordinates": {"lat": 30.04442, "lng": 31.235712},
        "payment_method": "cash_after_service",
        "services": [
            {
                "id": "svc-detail-001",
                "service_id": IDS["sub_category_id"],
                "service_name": "Deep Cleaning",
                "quantity": 1,
                "total_cost": 450.0,
            }
        ],
        "status_histories": [
            {"booking_status": status, "changed_by": IDS["supervisor_user_id"], "created_at": TS}
        ],
    }
    if with_team:
        b["team"] = team(with_members=True)
    return b


def provider_profile():
    # Lean supervisor-mode /provider/info shape (no commission/company/zone polygon).
    return {
        "id": IDS["provider_id"],
        "role": "supervisor",
        "first_name": "Mohamed",
        "last_name": "Ali",
        "full_name": "Mohamed Ali",
        "email": "ops.test@clean365.sa",
        "phone": "+966500000999",
        "profile_image_full_path": IMG,
        "zone": {
            "id": IDS["zone_id"],
            "name": "Cairo Central",
        },
        "is_active": 1,
        "is_approved": 1,
    }


def provider_dashboard():
    return [
        {
            "top_cards": {
                "total_earning": 28500.0,
                "total_subscribed_services": 12,
                "total_bookings": 156,
                "total_serviceman": 8,
            }
        },
        {
            "booking_stats": {
                "pending": 3,
                "accepted": 5,
                "ongoing": 2,
                "completed": 140,
                "canceled": 6,
            }
        },
        {
            "recent_bookings": [
                {
                    "id": IDS["booking_id"],
                    "readable_id": 100042,
                    "booking_status": "accepted",
                    "total_booking_amount": 450.0,
                    "service_schedule": "2026-07-12 09:00:00",
                    "customer": {"first_name": "Fatma", "last_name": "Ibrahim"},
                }
            ]
        },
    ]


def provider_config():
    return {
        "business_name": "Clean365",
        "currency_symbol": "EGP",
        "currency_code": "EGP",
        "country_code": "EG",
        "language_code": "en",
        "time_zone": "Africa/Cairo",
        "provider_app_version": {"android": "3.8.0", "ios": "3.8.0"},
        "serviceman_app_version": {"android": "3.8.0", "ios": "3.8.0"},
        "supervisor_mode": True,
        "check_in_radius_km": 1,
        "booking_statuses": [
            {"key": "pending", "value": "Pending"},
            {"key": "accepted", "value": "Accepted"},
            {"key": "ongoing", "value": "Ongoing"},
            {"key": "completed", "value": "Completed"},
            {"key": "canceled", "value": "Canceled"},
        ],
        "booking_task_statuses": [
            {"key": "pending", "value": "Pending"},
            {"key": "in_progress", "value": "In Progress"},
            {"key": "completed", "value": "Completed"},
        ],
    }


def booking_list_response():
    b = booking("accepted", with_team=True)
    return {
        "bookings_count": {
            "pending": 3,
            "accepted": 5,
            "ongoing": 2,
            "completed": 140,
            "canceled": 6,
        },
        "bookings": paginate([b], path="/api/v1/provider/booking", per_page=10),
    }


def serviceman_booking_detail(team_order=True):
    b = booking("ongoing" if team_order else "accepted", with_team=team_order)
    b["can_update_booking_status"] = not team_order
    return {
        "booking": b,
        "provider_serviceman_can_cancel_booking": 0,
        "provider_serviceman_can_edit_booking": 0,
    }


def checkin_attendee_response():
    att = task_attendee(attended=True)
    att["task"] = {"id": IDS["task_id"], "title": "Deep clean kitchen", "booking_id": IDS["booking_id"]}
    att["task"]["booking"] = {"id": IDS["booking_id"], "readable_id": 100042}
    return att


def ops_job_card(field_status="in_progress", progress=65):
    return {
        "id": IDS["booking_id"],
        "readable_id": "CLN-2458",
        "title": "Deep Cleaning",
        "field_status": field_status,
        "booking_status": "ongoing" if field_status in ("arrived", "in_progress") else (
            "completed" if field_status == "completed" else "accepted"
        ),
        "package_label": "Monthly package",
        "progress_percent": progress,
        "tasks_summary": {"completed": 2, "total": 3},
        "attendance": {"attended": 3, "total": 3},
        "schedule_at": "2026-06-27 16:00:00",
        "customer": {
            "id": IDS["customer_id"],
            "name": "Abdulrahman Jamil",
            "phone": "+966551234567",
        },
        "property_type": "Apartment",
        "location_text": "An Narjis District, Riyadh",
        "location": {"text": "An Narjis District, Riyadh", "lat": 24.8301, "lng": 46.7056},
        "coordinates": {"lat": 24.8301, "lng": 46.7056},
        "team": {
            "id": IDS["team_id"],
            "name": "Team 2",
            "members_count": 3,
            "members": [
                {"id": IDS["serviceman_id"], "name": "Sami Ali", "phone": "+966500000001", "profile_image": IMG},
                {"id": "c3d4e5f6-a7b8-9012-cdef-123456789099", "name": "Khaled Ahmed", "phone": "+966500000002", "profile_image": IMG},
                {"id": "c3d4e5f6-a7b8-9012-cdef-123456789088", "name": "Ahmed Hassan", "phone": "+966500000003", "profile_image": IMG},
            ],
        },
    }


def ops_job_detail(field_status="in_progress"):
    card = ops_job_card(field_status)
    card["customer_note"] = "Please focus on cleaning the kitchen and bathrooms, and ensure surfaces are sanitized."
    card["field_status_timeline"] = ["assigned", "on_the_way", "arrived", "in_progress", "completed"]
    card["photos"] = {
        "before": [TASK_IMG, TASK_IMG, TASK_IMG],
        "during": [TASK_IMG, TASK_IMG],
        "after": [],
        "counts": {"before": 3, "during": 2, "after": 0},
    }
    card["execution_notes"] = [
        {
            "id": "note-001",
            "note": "Started kitchen and bathroom cleaning; work is on plan.",
            "user_id": IDS["supervisor_user_id"],
            "user_name": "Mohamed Ali",
            "created_at": TS,
        }
    ]
    card["tasks"] = [
        {"id": IDS["task_id"], "title": "Kitchen deep clean", "status": "completed", "attendees_count": 2},
        {"id": "a7b8c9d0-e1f2-3456-0123-567890123457", "title": "Bathrooms sanitize", "status": "in_progress", "attendees_count": 2},
        {"id": "a7b8c9d0-e1f2-3456-0123-567890123458", "title": "Living room wipe", "status": "pending", "attendees_count": 1},
    ]
    card["team"]["attendance"] = card["attendance"]
    return card


def ops_team_card(status="in_progress"):
    return {
        "id": IDS["team_id"],
        "name": "Team 1",
        "is_active": status != "unavailable",
        "status": status,
        "technicians_count": 3,
        "members": [
            {"id": IDS["serviceman_id"], "name": "Khaled Ahmed", "phone": "+966500000002", "profile_image": IMG},
            {"id": "c3d4e5f6-a7b8-9012-cdef-123456789099", "name": "Sami Ali", "phone": "+966500000001", "profile_image": IMG},
            {"id": "c3d4e5f6-a7b8-9012-cdef-123456789088", "name": "Ahmed Hassan", "phone": "+966500000003", "profile_image": IMG},
        ],
        "current_job": ops_job_card("in_progress") if status in ("on_the_way", "in_progress") else None,
    }


def ops_dashboard():
    return {
        "today_summary": {
            "total": 12,
            "completed": 3,
            "upcoming": 5,
            "in_progress": 4,
            "progress_percent": 25,
            "date": "2026-06-27",
        },
        "teams_preview": [
            ops_team_card("on_the_way"),
            ops_team_card("in_progress"),
            {**ops_team_card("available"), "name": "Team 3", "technicians_count": 2, "current_job": None},
        ],
        "active_jobs": [ops_job_card("on_the_way", 10), ops_job_card("in_progress", 65)],
        "upcoming_jobs": [ops_job_card("assigned", 0)],
    }


def ops_jobs_list():
    return {
        "summary": {
            "total": 16,
            "progress_percent": 36,
            "assigned": 3,
            "on_the_way": 2,
            "arrived": 1,
            "in_progress": 4,
            "completed": 6,
        },
        "jobs": paginate([ops_job_card("in_progress")], path="/api/v1/provider/ops/jobs"),
    }


def ops_teams_list():
    return {
        "summary": {
            "total": 6,
            "available": 2,
            "in_progress": 3,
            "on_the_way": 1,
            "unavailable": 0,
        },
        "teams": {
            "data": [ops_team_card("in_progress"), ops_team_card("available"), ops_team_card("on_the_way")],
            "total": 3,
            "limit": 10,
            "offset": 1,
        },
    }


def notifications_list():
    return paginate([
        {
            "id": "notif-001",
            "title": "New booking assigned",
            "description": "Booking #CLN-2458 is pending acceptance in your zone.",
            "to_users": ["provider-admin"],
            "zone_ids": [IDS["zone_id"]],
            "cover_image_full_path": None,
            "created_at": TS,
        }
    ], path="/api/v1/provider/notifications")


# ── Pre-built envelope responses ───────────────────────────────────────────

R = {
    "login_supervisor_ok": envelope("auth_login_200", "successfully logged in", {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYWJjZGVm...",
        "is_active": 1,
    }),
    "login_serviceman_ok": envelope("auth_login_200", "successfully logged in", {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiZ2hpamts...",
        "is_active": 1,
    }),
    "login_401": envelope("auth_login_401", "user credential does not match"),
    "login_404": envelope("auth_login_404", "User does not exist"),
    "login_403": envelope("auth_login_403", "wrong login credentials", None, [
        {"error_code": "email_or_phone", "message": "The email or phone field is required."},
        {"error_code": "password", "message": "The password field is required."},
    ]),
    "logout_ok": envelope("auth_logout_200", "successfully logged out"),
    "provider_profile": envelope("default_200", "successfully data fetched", provider_profile()),
    "provider_dashboard": envelope("default_200", "successfully data fetched", provider_dashboard()),
    "provider_config": envelope("default_200", "successfully data fetched", provider_config()),
    "serviceman_list": envelope("default_200", "successfully data fetched", paginate([lean_serviceman()])),
    "serviceman_show": envelope("default_200", "successfully data fetched", serviceman_show()),
    "serviceman_store": envelope("default_store_200", "successfully added"),
    "serviceman_update": envelope("default_update_200", "successfully updated"),
    "serviceman_status": envelope("default_status_update_200", "status updated successfully"),
    "serviceman_email_taken": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "email", "message": "Email already taken"},
    ]),
    "team_list": envelope("default_200", "successfully data fetched", paginate([team()], path="/api/v1/provider/team")),
    "team_show": envelope("default_200", "successfully data fetched", team()),
    "team_store": envelope("default_store_200", "successfully added", team()),
    "team_update": envelope("default_update_200", "successfully updated", team()),
    "team_delete": envelope("default_delete_200", "successfully deleted"),
    "team_assign": envelope("team_assigned_to_booking_200", "Team assigned to booking successfully", booking("accepted", with_team=True)),
    "team_invalid_serviceman": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "invalid_serviceman", "message": "One or more servicemen do not belong to this supervisor"},
    ]),
    "team_empty": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "empty_team", "message": "Team must have at least one serviceman"},
    ]),
    "team_in_use": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "team_in_use", "message": "Team is assigned to active bookings and cannot be deleted"},
    ]),
    "task_list": envelope("default_200", "successfully data fetched", [booking_task("pending"), booking_task("in_progress", with_attendees=True)]),
    "task_show": envelope("default_200", "successfully data fetched", booking_task("in_progress")),
    "task_store": envelope("default_store_200", "successfully added", booking_task("pending")),
    "task_update": envelope("default_update_200", "successfully updated", booking_task("in_progress")),
    "task_delete": envelope("default_delete_200", "successfully deleted"),
    "task_invalid_attendee": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "invalid_attendee", "message": "Attendees must belong to the assigned team or supervisor"},
    ]),
    "task_status_ok": envelope("task_status_update_success_200", "Task status updated successfully", booking_task("in_progress")),
    "task_notes_ok": envelope("default_update_200", "successfully updated", task_attendee(attended=True)),
    "task_checkin_ok": envelope("task_check_in_success_200", "Attendance recorded successfully", checkin_attendee_response()),
    "task_checkin_already": envelope("task_already_checked_in_200", "Attendance already recorded", task_attendee(attended=True)),
    "task_too_far": envelope("task_check_in_too_far_403", "You must be within 1 km of the booking location to check in", {
        "distance_km": 2.412,
        "allowed_radius_km": 1,
    }),
    "booking_list": envelope("default_200", "successfully data fetched", booking_list_response()),
    "booking_show": envelope("default_200", "successfully data fetched", booking("accepted", with_team=True)),
    "booking_status_ok": envelope("status_update_success_200", "booking status updated successfully", booking("ongoing", with_team=True)),
    "booking_accept_ok": envelope("status_update_success_200", "booking status updated successfully", booking("accepted", with_team=False)),
    "booking_assign_serviceman": envelope("default_200", "successfully data fetched", {
        **booking("accepted", with_team=False),
        "serviceman_id": IDS["serviceman_id"],
    }),
    "serviceman_booking_list": envelope("default_200", "successfully data fetched", paginate([
        booking("ongoing", with_team=True),
    ], path="/api/v1/serviceman/booking/list")),
    "serviceman_booking_detail_team": envelope("default_200", "successfully data fetched", serviceman_booking_detail(team_order=True)),
    "serviceman_booking_detail_solo": envelope("default_200", "successfully data fetched", serviceman_booking_detail(team_order=False)),
    "serviceman_task_list": envelope("default_200", "successfully data fetched", paginate([
        booking_task("in_progress"),
    ], path="/api/v1/serviceman/task")),
    "serviceman_task_show": envelope("default_200", "successfully data fetched", booking_task("in_progress")),
    "booking_supervisor_only": envelope("booking_status_supervisor_only_403", "Only the supervisor can update booking status for team orders"),
    "not_found_204": envelope("default_204", "information not found"),
    "validation_400": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "limit", "message": "The limit field is required."},
        {"error_code": "offset", "message": "The offset field is required."},
    ]),
    "validation_task_title": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "title", "message": "The title field is required."},
    ]),
    "unauthenticated": {"message": "Unauthenticated."},
    "provider_notifications": envelope("default_200", "successfully data fetched", notifications_list()),
    "provider_fcm_ok": envelope("default_update_200", "successfully updated"),
    "provider_password_ok": envelope("default_update_200", "successfully updated"),
    "provider_profile_update_ok": envelope("default_update_200", "successfully updated", provider_profile()),
    "ops_dashboard": envelope("default_200", "successfully data fetched", ops_dashboard()),
    "ops_teams_list": envelope("default_200", "successfully data fetched", ops_teams_list()),
    "ops_team_show": envelope("default_200", "successfully data fetched", {
        **ops_team_card("in_progress"),
        "current_job": ops_job_detail("in_progress"),
    }),
    "ops_jobs_list": envelope("default_200", "successfully data fetched", ops_jobs_list()),
    "ops_job_detail": envelope("default_200", "successfully data fetched", ops_job_detail("in_progress")),
    "ops_field_status_ok": envelope("default_update_200", "successfully updated", ops_job_detail("in_progress")),
    "ops_progress_ok": envelope("default_update_200", "successfully updated", ops_job_card("in_progress", 70)),
    "ops_progress_blocked": envelope("default_400", "invalid or missing information", None, [
        {"error_code": "progress_percent", "message": "Progress is derived from booking tasks and cannot be set manually"},
    ]),
    "ops_images_ok": envelope("default_store_200", "successfully added", ops_job_detail("in_progress")),
    "ops_note_ok": envelope("default_store_200", "successfully added", {
        "id": "note-002",
        "note": "Kitchen area completed.",
        "user_id": IDS["supervisor_user_id"],
        "user_name": "Mohamed Ali",
        "created_at": TS,
    }),
    "ops_attendance": envelope("default_200", "successfully data fetched", {
        "summary": {"attended": 3, "total": 3},
        "members": [
            {"id": IDS["serviceman_id"], "name": "Sami Ali", "is_attended": True, "checked_in_at": TS},
            {"id": "c3d4e5f6-a7b8-9012-cdef-123456789099", "name": "Khaled Ahmed", "is_attended": True, "checked_in_at": TS},
            {"id": "c3d4e5f6-a7b8-9012-cdef-123456789088", "name": "Ahmed Hassan", "is_attended": True, "checked_in_at": TS},
        ],
    }),
    "ops_support_ok": envelope("default_store_200", "successfully added", {
        "id": "support-001",
        "type": "support",
        "message": "Need extra mop kits for Team 2",
        "status": "open",
        "created_at": TS,
    }),
    "task_supervisor_only_403": envelope("task_supervisor_only_403", "Only the supervisor can update task status, notes, or review images"),
    "serviceman_admin_only_403": envelope("serviceman_admin_only_403", "Servicemen can only be created, updated, or deleted by admin"),
    "admin_assignment_only_403": envelope("admin_assignment_only_403", "Admin assigns the supervisor, team, and tasks. Supervisors can only update task status, notes, and photos"),
}
