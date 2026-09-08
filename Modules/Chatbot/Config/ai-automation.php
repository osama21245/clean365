<?php

/**
 * Chat LLM config (Gemini → OpenAI failover), aligned with Factor/Elsorady.
 * Keys fall back to existing Clean365 services.gemini / OPENAI_API_KEY.
 */
return [
    'gemini_text_api_key' => env('GEMINI_TEXT_API_KEY')
        ?: env('GEMINI_VERTEX_API_KEY')
        ?: env('GEMINI_API_KEY'),
    'gemini_text_api_base' => env(
        'GEMINI_TEXT_BASE_URL',
        'https://aiplatform.googleapis.com/v1/publishers/google/models'
    ),
    'gemini_text_api_method' => env('GEMINI_TEXT_API_METHOD', 'streamGenerateContent'),
    'gemini_use_response_mime_type' => filter_var(env('GEMINI_USE_RESPONSE_MIME_TYPE', 'false'), FILTER_VALIDATE_BOOLEAN),
    'gemini_text_google_search' => filter_var(env('GEMINI_TEXT_GOOGLE_SEARCH', 'false'), FILTER_VALIDATE_BOOLEAN),
    'gemini_text_timeout' => (int) env('GEMINI_HTTP_TIMEOUT', 180),
    'gemini_text_top_p' => (float) env('GEMINI_TEXT_TOP_P', 1),
    'gemini_text_model' => env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash'),
    'chat_model' => env('GEMINI_CHAT_MODEL', env('GEMINI_TEXT_MODEL', 'gemini-2.5-flash')),
    'chat_models' => array_values(array_filter(array_map(
        'trim',
        explode(',', env(
            'GEMINI_CHAT_MODELS',
            env('GEMINI_TEXT_MODELS', 'gemini-2.5-flash,gemini-2.5-flash-lite,gemini-2.0-flash')
        ))
    ))),
    'chat_temperature' => (float) env('CHATBOT_TEMPERATURE', 0.35),
    'chat_max_output_tokens' => (int) env('CHATBOT_MAX_OUTPUT_TOKENS', 4096),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_text_model' => env('OPENAI_CHAT_MODEL', env('OPENAI_TEXT_MODEL', 'gpt-4o-mini')),
    'pinecone_dispatch_sync' => filter_var(env('AI_PINECONE_SYNC', true), FILTER_VALIDATE_BOOLEAN),
];
