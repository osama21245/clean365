<?php

return [
    'name' => 'Chatbot',
    'frontend_url' => env('FRONTEND_URL', env('APP_URL', 'https://clean365.sa')),
    'deep_link_scheme' => env('CHATBOT_DEEP_LINK_SCHEME', 'clean365'),
    'pinecone_dispatch_sync' => filter_var(env('AI_PINECONE_SYNC', true), FILTER_VALIDATE_BOOLEAN),
];
