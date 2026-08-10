<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Segment extends Model
{
    protected static string $table = 'segments';
    protected static bool $timestamps = false;

    public static function findByKey(string $key): ?array
    {
        return static::firstWhere(['key' => $key]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function allWithCounts(): array
    {
        return static::db()->select('SELECT * FROM segments ORDER BY name');
    }

    /**
     * Replaces this segment's membership atomically — clears and re-inserts
     * so a customer who no longer qualifies is dropped on the next run.
     * @param array<int, int> $userIds
     */
    public static function syncMembers(int $segmentId, array $userIds): void
    {
        $db = static::db();
        $db->delete('segment_members', 'segment_id = :sid', ['sid' => $segmentId]);

        foreach (array_unique($userIds) as $userId) {
            $db->insert('segment_members', ['segment_id' => $segmentId, 'user_id' => $userId, 'added_at' => now()]);
        }

        $db->update('segments', ['member_count' => count(array_unique($userIds)), 'updated_at' => now()], 'id = :id', ['id' => $segmentId]);
    }

    /** @return array<int, int> */
    public static function memberIds(int $segmentId): array
    {
        $rows = static::db()->select('SELECT user_id FROM segment_members WHERE segment_id = :sid', ['sid' => $segmentId]);
        return array_map(static fn (array $r) => (int) $r['user_id'], $rows);
    }
}
