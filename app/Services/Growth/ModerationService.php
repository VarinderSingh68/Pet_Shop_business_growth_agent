<?php

declare(strict_types=1);

namespace App\Services\Growth;

/**
 * Lightweight keyword-based abusive-language check for review text. Not a
 * substitute for real moderation — it just catches the obvious cases and
 * routes them to manual review, which is what "auto-flag" means here.
 */
final class ModerationService
{
    /** @var array<int, string> */
    private const BLOCKLIST = [
        'idiot', 'stupid', 'garbage', 'trash', 'scam', 'fraud', 'hate',
        'terrible seller', 'shut up', 'kill yourself', 'worthless',
    ];

    public function isAbusive(string $text): bool
    {
        return $this->matchedTerm($text) !== null;
    }

    public function matchedTerm(string $text): ?string
    {
        $normalized = strtolower($text);

        foreach (self::BLOCKLIST as $term) {
            if (str_contains($normalized, $term)) {
                return $term;
            }
        }

        return null;
    }
}
