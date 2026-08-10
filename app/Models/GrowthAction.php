<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class GrowthAction extends Model
{
    protected static string $table = 'growth_actions';
    protected static bool $timestamps = false;

    public static function log(string $actionType, string $explanation, array $extra = []): int
    {
        return static::create([
            'action_type' => $actionType,
            'explanation' => $explanation,
            'target_type' => $extra['target_type'] ?? null,
            'target_id' => $extra['target_id'] ?? null,
            'affected_count' => $extra['affected_count'] ?? null,
            'estimated_impact_paise' => $extra['estimated_impact_paise'] ?? null,
            'status' => $extra['status'] ?? 'executed',
            'executed_at' => now(),
            'created_at' => now(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function recent(int $limit = 30): array
    {
        return static::db()->select('SELECT * FROM growth_actions ORDER BY id DESC LIMIT ' . max(1, $limit));
    }
}
