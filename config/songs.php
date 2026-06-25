<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Song / karaoke request queue
    |--------------------------------------------------------------------------
    |
    | Maximum number of active (queued or playing) requests a single table
    | session may hold at once — the proposal's Qmax.
    |
    */
    'queue_max' => env('SONG_QUEUE_MAX', 2),
];
