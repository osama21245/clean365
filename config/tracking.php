<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Field agent online threshold (minutes)
    |--------------------------------------------------------------------------
    | A supervisor or serviceman is "online" when last_seen_at is within this window.
    */
    'online_threshold_minutes' => (int) env('FIELD_AGENT_ONLINE_THRESHOLD_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Location write rate limit
    |--------------------------------------------------------------------------
    */
    'location_write' => [
        'max_per_minute' => (int) env('LOCATION_WRITE_MAX_PER_MINUTE', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Busy field statuses
    |--------------------------------------------------------------------------
    | Bookings in these field statuses (and not completed/canceled) mark agents busy.
    */
    'busy_field_statuses' => ['on_the_way', 'arrived', 'in_progress'],

    /*
    |--------------------------------------------------------------------------
    | Customer agent location (live map pin for customer app)
    |--------------------------------------------------------------------------
    | Customer may read the primary agent GPS only while the booking is "en route"
    | (same statuses as busy by default). Poll GET .../booking/{id}/agent-location.
    */
    'customer_agent_location' => [
        'enabled' => (bool) env('CUSTOMER_AGENT_LOCATION_ENABLED', true),
        'visible_field_statuses' => ['on_the_way', 'arrived', 'in_progress'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Live location broadcasting (Reverb / Pusher protocol)
    |--------------------------------------------------------------------------
    | When enabled, GPS writes and field-status transitions broadcast on:
    | - private-booking.{bookingId}.tracking
    | - private-admin.live-map
    */
    'broadcast' => [
        'enabled' => (bool) env('TRACKING_BROADCAST_ENABLED', true),
        'throttle_seconds' => (float) env('TRACKING_BROADCAST_THROTTLE_SECONDS', 3),
        'min_move_meters' => (float) env('TRACKING_BROADCAST_MIN_MOVE_METERS', 15),
    ],
];
