<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ephemeral table chat (Redis-backed)
    |--------------------------------------------------------------------------
    |
    | Two conversation kinds: each table's own private room, and WhatsApp-style
    | direct chats between two occupied tables. Messages live only in Redis with
    | a TTL — never persisted to the domain database — and each conversation's
    | list is capped so memory stays bounded.
    |
    */
    'redis_connection' => env('CHAT_REDIS_CONNECTION', 'default'),

    'key_prefix' => 'chat:lobby',

    // How long a room's messages survive without activity.
    'ttl_minutes' => env('CHAT_TTL_MINUTES', 360),

    // Maximum number of recent messages kept per room (ring buffer).
    'max_messages' => env('CHAT_MAX_MESSAGES', 100),

    'max_length' => 280,

    // Words masked by the profanity filter (case-insensitive, substring match).
    'profanity' => [
        'anjing', 'bangsat', 'kontol', 'memek', 'bajingan', 'goblok', 'tolol',
        'kampret', 'asu', 'babi', 'tai', 'jancok', 'ngentot', 'fuck', 'shit', 'bitch',
    ],
];
