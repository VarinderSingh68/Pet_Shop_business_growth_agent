<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Order extends Model
{
    protected static string $table = 'orders';
    protected static bool $softDeletes = true;

    public static function findByNumber(string $orderNumber): ?array
    {
        return static::firstWhere(['order_number' => strtoupper(trim($orderNumber))]);
    }

    public static function generateOrderNumber(): string
    {
        return 'HT-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    /** @return array<int, array<string, mixed>> */
    public static function items(int $orderId): array
    {
        return static::db()->select(
            'SELECT * FROM order_items WHERE order_id = :oid ORDER BY id ASC',
            ['oid' => $orderId],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function statusHistory(int $orderId): array
    {
        return static::db()->select(
            'SELECT * FROM order_status_history WHERE order_id = :oid ORDER BY id ASC',
            ['oid' => $orderId],
        );
    }

    public static function addStatusHistory(int $orderId, string $status, ?string $note = null, ?int $changedByUserId = null): void
    {
        static::db()->insert('order_status_history', [
            'order_id' => $orderId,
            'status' => $status,
            'note' => $note,
            'changed_by_user_id' => $changedByUserId,
            'created_at' => now(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::where(['user_id' => $userId], 'created_at DESC');
    }
}
