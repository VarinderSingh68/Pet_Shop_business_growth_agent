<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class DeliveryAssignment extends Model
{
    protected static string $table = 'delivery_assignments';

    public const STATUSES = ['assigned', 'picked_up', 'out_for_delivery', 'delivered', 'failed'];

    /** Open assignments are still on the road — not yet delivered or failed. */
    public const OPEN_STATUSES = ['assigned', 'picked_up', 'out_for_delivery'];

    /** @return array<string, mixed>|null the current (most recent) assignment for an order */
    public static function currentForOrder(int $orderId): ?array
    {
        $rows = static::where(['order_id' => $orderId], 'id DESC', 1);
        return $rows[0] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>> assigned orders for a rider, joined with the
     * order fields the app needs, newest assignment first
     */
    public static function forPartner(int $partnerId, ?string $status = null): array
    {
        $sql = "SELECT da.*, o.order_number, o.status AS order_status, o.total_paise, o.currency,
                       o.payment_method, o.payment_status,
                       o.shipping_full_name, o.shipping_phone, o.shipping_line1, o.shipping_line2,
                       o.shipping_city, o.shipping_state, o.shipping_postal_code, o.shipping_country,
                       o.customer_notes
                FROM delivery_assignments da
                JOIN orders o ON o.id = da.order_id
                WHERE da.delivery_partner_id = :partner_id AND o.deleted_at IS NULL";

        $bindings = ['partner_id' => $partnerId];

        if ($status !== null && $status !== '') {
            $sql .= ' AND da.status = :status';
            $bindings['status'] = $status;
        }

        $sql .= ' ORDER BY da.assigned_at DESC';

        return static::db()->select($sql, $bindings);
    }

    /** @return array<string, mixed>|null one assignment, but only if it belongs to this rider */
    public static function forPartnerAndOrder(int $partnerId, int $orderId): ?array
    {
        return static::db()->selectOne(
            'SELECT * FROM delivery_assignments WHERE delivery_partner_id = :p AND order_id = :o ORDER BY id DESC LIMIT 1',
            ['p' => $partnerId, 'o' => $orderId],
        );
    }
}
