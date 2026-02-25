<?php

namespace App\Service;

final class CommentSanitizer
{
    public function __construct(private readonly array $badWords)
    {
    }

    public function sanitize(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $wordSet = [];
        foreach ($this->badWords as $word) {
            if (!is_string($word)) {
                continue;
            }

            $normalizedWord = trim($word);
            if ($normalizedWord !== '') {
                $wordSet[$normalizedWord] = true;
            }
        }

        if ($wordSet === []) {
            return $text;
        }

        $escapedWords = array_map(
            static fn (string $word): string => preg_quote($word, '/'),
            array_keys($wordSet)
        );
        $pattern = '/(?<![\p{L}\p{N}_])(?:' . implode('|', $escapedWords) . ')(?![\p{L}\p{N}_])/iu';

        $sanitizedText = preg_replace_callback(
            $pattern,
            static function (array $matches): string {
                $matchedWord = $matches[0] ?? '';
                $length = function_exists('mb_strlen') ? mb_strlen($matchedWord) : strlen($matchedWord);

                return str_repeat('*', $length);
            },
            $text
        );

        return is_string($sanitizedText) ? $sanitizedText : $text;
    }
}
