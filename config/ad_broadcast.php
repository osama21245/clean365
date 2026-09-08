<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FCM token chunk size (HTTP v1 multicast)
    |--------------------------------------------------------------------------
    */
    'chunk_size' => (int) env('AD_BROADCAST_CHUNK_SIZE', 500),

    /*
    |--------------------------------------------------------------------------
    | Max dashboard publishes per admin per minute
    |--------------------------------------------------------------------------
    */
    'rate_limit_per_minute' => (int) env('AD_BROADCAST_RATE_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Max zones selectable in one broadcast (guardrail)
    |--------------------------------------------------------------------------
    */
    'max_zones' => (int) env('AD_BROADCAST_MAX_ZONES', 50),

    /*
    |--------------------------------------------------------------------------
    | Max explicit user IDs per targeted broadcast (guardrail)
    |--------------------------------------------------------------------------
    */
    'max_explicit_users' => (int) env('AD_BROADCAST_MAX_EXPLICIT_USERS', 5000),

    /*
    |--------------------------------------------------------------------------
    | Queue name for batch FCM jobs (empty = default queue)
    |--------------------------------------------------------------------------
    */
    'queue' => env('AD_BROADCAST_QUEUE'),

    /*
    |--------------------------------------------------------------------------
    | Audiences (Clean365 user_type mapping)
    |--------------------------------------------------------------------------
    |
    | customers  → users.user_type = customer
    | providers  → users.user_type = provider-admin
    | servicemen → users.user_type = provider-serviceman
    | guests     → FCM topic "guest" only (no user rows)
    |
    | Zone-scoped FCM topics:
    |   customer-{zone_id}
    |   provider-admin-{zone_id}
    |   provider-serviceman-{zone_id}
    |
    | Global (no zones selected): customer / provider-admin / provider-serviceman / guest
    |
    */
    'audiences' => ['customers', 'providers', 'servicemen', 'guests'],

    /*
    |--------------------------------------------------------------------------
    | Locales generated for AI push (stored as JSON on ad_broadcasts.title)
    |--------------------------------------------------------------------------
    */
    'push_locales' => env('AD_BROADCAST_PUSH_LOCALES') !== null && env('AD_BROADCAST_PUSH_LOCALES') !== ''
        ? array_values(array_filter(array_map('trim', explode(',', (string) env('AD_BROADCAST_PUSH_LOCALES')))))
        : ['ar', 'en'],

    /*
    | Creative AI pass: ar+en only (fast). Other push_locales are filled in one translate pass.
    */
    'push_primary_locales' => env('AD_BROADCAST_PUSH_PRIMARY_LOCALES') !== null && env('AD_BROADCAST_PUSH_PRIMARY_LOCALES') !== ''
        ? array_values(array_filter(array_map('trim', explode(',', (string) env('AD_BROADCAST_PUSH_PRIMARY_LOCALES')))))
        : ['ar', 'en'],

    'push_translate_secondary' => filter_var(env('AD_BROADCAST_PUSH_TRANSLATE_SECONDARY', true), FILTER_VALIDATE_BOOL),

    /*
    | Fall back to OpenAI when Gemini fails for AI push copy.
    | Keep false unless OpenAI has billing/credits — otherwise failures show misleading 429s.
    */
    'ai_push_openai_fallback' => filter_var(env('AI_PUSH_OPENAI_FALLBACK', false), FILTER_VALIDATE_BOOL),

    /*
    | Fallback when no users.fcm_token for an audience (devices subscribed only to FCM topic).
    */
    'always_send_global_topic' => filter_var(env('AD_BROADCAST_ALWAYS_SEND_GLOBAL_TOPIC', true), FILTER_VALIDATE_BOOL),

    /*
    | Customers / providers / servicemen: send via fcm_token multicast (locale from app locale).
    */
    'prefer_fcm_token_multicast' => filter_var(env('AD_BROADCAST_PREFER_FCM_TOKEN_MULTICAST', true), FILTER_VALIDATE_BOOL),

    /*
    | Do not also send the global topic when tokens were targeted (avoids duplicate notifications).
    */
    'skip_topic_when_tokens_sent' => filter_var(env('AD_BROADCAST_SKIP_TOPIC_WHEN_TOKENS_SENT', true), FILTER_VALIDATE_BOOL),

    /*
    | Save a row in user_inbox_notifications per targeted user.
    */
    'persist_in_app_notifications' => filter_var(env('AD_BROADCAST_PERSIST_IN_APP_NOTIFICATIONS', true), FILTER_VALIDATE_BOOL),

    /*
    | Run AI generation inline in the scheduler command (true = old behaviour). false = queue ProcessScheduledAiPushJob.
    */
    'ai_push_run_sync' => filter_var(env('AI_PUSH_RUN_SYNC', false), FILTER_VALIDATE_BOOL),

    /*
    | Queue for ProcessScheduledAiPushJob when ai_push_run_sync=false.
    */
    'ai_push_queue' => env('AI_PUSH_QUEUE', env('AD_BROADCAST_QUEUE')),

    /*
    | Clock-based AI push (daily/weekly/…): after this many minutes past a scheduled
    | HH:MM slot, skip it instead of catching up (prevents notification bursts after outages).
    */
    'ai_push_slot_grace_minutes' => max(1, (int) env('AI_PUSH_SLOT_GRACE_MINUTES', 30)),

];
