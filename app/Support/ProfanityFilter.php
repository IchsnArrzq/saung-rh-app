<?php

namespace App\Support;

class ProfanityFilter
{
    /**
     * Mask any configured profane words (case-insensitive) with asterisks,
     * keeping the first character so the message stays readable.
     */
    public static function clean(string $text): string
    {
        $words = (array) config('chat.profanity', []);

        foreach ($words as $word) {
            $word = trim((string) $word);

            if ($word === '') {
                continue;
            }

            $text = preg_replace_callback(
                '/'.preg_quote($word, '/').'/iu',
                fn (array $m): string => mb_substr($m[0], 0, 1).str_repeat('*', max(1, mb_strlen($m[0]) - 1)),
                $text,
            ) ?? $text;
        }

        return $text;
    }
}
