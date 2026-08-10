<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class CustomerScore extends Model
{
    protected static string $table = 'customer_scores';
    protected static bool $timestamps = false;

    public static function forUser(int $userId): ?array
    {
        return static::firstWhere(['user_id' => $userId]);
    }

    public static function upsert(int $userId, array $data): void
    {
        $existing = static::forUser($userId);
        $data['user_id'] = $userId;
        $data['calculated_at'] = now();

        if ($existing !== null) {
            static::db()->update('customer_scores', $data, 'id = :id', ['id' => $existing['id']]);
        } else {
            static::db()->insert('customer_scores', $data);
        }
    }
}
