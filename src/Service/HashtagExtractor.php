<?php

namespace App\Service;

final class HashtagExtractor
{
    /**
     * @return string[]
     */
    public function extractFromText(?string $text): array
    {
        if ($text === null || $text === '') {
            return [];
        }

        preg_match_all('/(?<![\p{L}\p{N}_])#([\p{L}\p{N}_]{1,50})/u', $text, $matches);
        if (!isset($matches[1]) || !is_array($matches[1])) {
            return [];
        }

        $hashtags = [];
        foreach ($matches[1] as $rawTag) {
            $normalized = $this->normalize($rawTag);
            if ($normalized === null || isset($hashtags[$normalized])) {
                continue;
            }

            $hashtags[$normalized] = true;
        }

        return array_keys($hashtags);
    }

    public function normalize(?string $rawHashtag): ?string
    {
        if ($rawHashtag === null) {
            return null;
        }

        $candidate = trim($rawHashtag);
        if ($candidate === '') {
            return null;
        }

        if (str_starts_with($candidate, '#')) {
            $candidate = substr($candidate, 1);
        }

        if ($candidate === '') {
            return null;
        }

        if (!preg_match('/^[\p{L}\p{N}_]{1,50}$/u', $candidate)) {
            return null;
        }

        return mb_strtolower($candidate, 'UTF-8');
    }
}
