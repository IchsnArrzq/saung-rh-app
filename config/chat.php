<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ephemeral inter-table chat (Redis-backed)
    |--------------------------------------------------------------------------
    |
    | Messages live only in Redis with a TTL — they are never persisted to the
    | domain database. The lobby list is capped so memory stays bounded.
    |
    */
    'redis_connection' => env('CHAT_REDIS_CONNECTION', 'default'),

    'key_prefix' => 'chat:lobby',

    // How long the lobby (and its reports) survive without activity.
    'ttl_minutes' => env('CHAT_TTL_MINUTES', 360),

    // Maximum number of recent messages kept in the lobby ring buffer.
    'max_messages' => env('CHAT_MAX_MESSAGES', 100),

    'max_length' => 280,

    // Upheld reports against a table before it is auto-blocked from chatting.
    'report_threshold' => env('CHAT_REPORT_THRESHOLD', 3),

    // Words masked by the profanity filter (case-insensitive, substring match).
    'profanity' => [
        'anjing', 'bangsat', 'kontol', 'memek', 'bajingan', 'goblok', 'tolol',
        'kampret', 'asu', 'babi', 'tai', 'jancok', 'ngentot', 'fuck', 'shit', 'bitch',
    ],
];
