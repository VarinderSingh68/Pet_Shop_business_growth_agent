<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    protected static string $table = 'categories';

    public static function findBySlug(string $slug): ?array
    {
        return static::firstWhere(['slug' => $slug]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function topLevel(): array
    {
        return static::db()->select(
            'SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name',
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function allActive(): array
    {
        return static::db()->select(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name',
        );
    }

    /**
     * All descendant category ids (including the given one) — a shallow tree
     * is enough here, so a couple of extra passes is fine over recursion.
     * @return array<int, int>
     */
    public static function withDescendants(int $categoryId): array
    {
        $ids = [$categoryId];
        $frontier = [$categoryId];

        while ($frontier !== []) {
            $placeholders = implode(',', array_fill(0, count($frontier), '?'));
            $rows = static::db()->select(
                "SELECT id FROM categories WHERE parent_id IN ({$placeholders})",
                $frontier,
            );
            $frontier = array_map(static fn (array $r) => (int) $r['id'], $rows);
            $ids = [...$ids, ...$frontier];
        }

        return $ids;
    }
}
