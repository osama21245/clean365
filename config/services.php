<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net')],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN')],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'vertex_api_key' => env('GEMINI_VERTEX_API_KEY'),
        'text_api_key' => env('GEMINI_TEXT_API_KEY'),
        'text_base_url' => env(
            'GEMINI_TEXT_BASE_URL',
            'https://generativelanguage.googleapis.com/v1beta'
        ),
        'text_endpoint_style' => env('GEMINI_TEXT_ENDPOINT_STYLE', 'generativelanguage'),
        'text_top_p' => (float) env('GEMINI_TEXT_TOP_P', 1),
        'text_google_search' => filter_var(env('GEMINI_TEXT_GOOGLE_SEARCH', 'false'), FILTER_VALIDATE_BOOLEAN),
        'image_api_key' => env('GEMINI_IMAGE_API_KEY'),
        'image_base_url' => env(
            'GEMINI_IMAGE_BASE_URL',
            'https://generativelanguage.googleapis.com/v1beta'
        ),
        'image_endpoint_style' => env('GEMINI_IMAGE_ENDPOINT_STYLE', 'generativelanguage'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-image'),
        'image_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', env(
                'GEMINI_IMAGE_MODELS',
                'gemini-3.1-flash-image,gemini-2.5-flash-image'
            ))
        ))),
        'text_model' => env('GEMINI_TEXT_MODEL', 'gemini-3.6-flash'),
        'seo_text_model' => env('GEMINI_SEO_TEXT_MODEL', 'gemini-3.6-flash'),
        'seo_text_api_key' => env('GEMINI_SEO_TEXT_API_KEY'),
        'seo_text_endpoint_style' => env('GEMINI_SEO_TEXT_ENDPOINT_STYLE'),
        'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 32768),
        'use_response_mime_type' => filter_var(env('GEMINI_USE_RESPONSE_MIME_TYPE', 'false'), FILTER_VALIDATE_BOOLEAN),
        'thinking_budget' => env('GEMINI_THINKING_BUDGET'),
        'http_timeout' => (int) env('GEMINI_HTTP_TIMEOUT', 180),
        'connect_timeout' => (int) env('GEMINI_CONNECT_TIMEOUT', 60),
        'text_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', env(
                'GEMINI_TEXT_MODELS',
                'gemini-3.6-flash,gemini-flash-latest,gemini-2.5-flash'
            ))
        ))),
        'seo_text_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', env(
                'GEMINI_SEO_TEXT_MODELS',
                'gemini-3.6-flash,gemini-flash-latest,gemini-2.5-flash'
            ))
        ))),
        'push_translate_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', env(
                'GEMINI_PUSH_TRANSLATE_MODELS',
                'gemini-3.6-flash,gemini-flash-latest'
            ))
        ))),
        'project_name' => env('GEMINI_PROJECT_NAME'),
    ],

    'image_generation' => [
        'provider' => env('IMAGE_PROVIDER', 'gemini'),
        'brand_primary_hex' => env('IMAGE_BRAND_PRIMARY_HEX', '#008080'),
        'brand_secondary_hex' => env('IMAGE_BRAND_SECONDARY_HEX', '#1a2b48'),
        'gemini_only' => filter_var(env('IMAGE_GENERATION_GEMINI_ONLY', 'false'), FILTER_VALIDATE_BOOLEAN),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1.5'),
            'size' => env('OPENAI_IMAGE_SIZE', '1024x1024'),
            'quality' => env('OPENAI_IMAGE_QUALITY', 'high'),
        ],
        'gemini_image_timeout' => (int) env('GEMINI_IMAGE_HTTP_TIMEOUT', 240),
        'pollinations_timeout' => (int) env('POLLINATIONS_IMAGE_HTTP_TIMEOUT', 120),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'chat_model' => env('OPENAI_CHAT_MODEL', env('OPENAI_TEXT_MODEL', 'gpt-4o-mini')),
    ],

    'pinecone' => [
        'api_key' => env('PINECONE_API_KEY'),
        'index_host' => env('PINECONE_INDEX_HOST'),
        'index_name' => env('PINECONE_INDEX_NAME', 'clean365'),
        'namespace' => env('PINECONE_NAMESPACE', 'packages'),
        'api_version' => env('PINECONE_API_VERSION', '2025-10'),
        'record_text_key' => env('PINECONE_RECORD_TEXT_KEY', 'text'),
    ],

    'firebase_dashboard_push' => [
        'primary_credentials' => env('FIREBASE_CREDENTIALS', ''),
        'secondary_credentials' => env('FIREBASE_SECONDARY_CREDENTIALS', ''),
        'fallback_enabled' => env('FIREBASE_DASHBOARD_FALLBACK_ENABLED', true),
        'retry_transient_once' => env('FIREBASE_DASHBOARD_RETRY_TRANSIENT_ONCE', true),
        'apns_enabled' => filter_var(env('FIREBASE_APNS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'apns_sound' => env('FIREBASE_APNS_SOUND', 'default'),
    ],
];
