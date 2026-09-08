<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Booking auto-assign (supervisors)
    |--------------------------------------------------------------------------
    |
    | Isolated config for the auto-assign system. Swap strategies / flags here
    | without touching booking create controllers.
    |
    */

    'enabled' => env('BOOKING_AUTO_ASSIGN_ENABLED', true),

    /*
    | Only run when supervisor mode is on (providers = supervisors).
    */
    'require_supervisor_mode' => true,

    /*
    | Prefer supervisors in the same zone as the booking, then fall back to any.
    */
    'prefer_same_zone' => true,

    /*
    | Also attach an available team under the chosen supervisor (needed for Ops jobs).
    | Set false to only assign provider_id + accepted status.
    */
    'assign_available_team' => true,

    /*
    | Booking statuses considered "open load" when scoring how busy a supervisor is.
    */
    'open_booking_statuses' => ['pending', 'accepted', 'ongoing'],

    /*
    | Field statuses that make a team "busy" (not preferred for auto team assign).
    */
    'busy_team_field_statuses' => ['on_the_way', 'arrived', 'in_progress'],

    /*
    | Value stored on bookings.assigned_by when auto-assigned.
    */
    'assigned_by' => 'auto',

    /*
    | Strategy class used to pick a supervisor. Replace this for future algorithms.
    */
    'picker' => \Modules\ServicemanModule\Services\BookingAutoAssign\SimpleLeastLoadSupervisorPicker::class,
];
