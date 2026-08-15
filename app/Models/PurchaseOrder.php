<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PurchaseOrder extends Model
{
    protected static string $table = 'purchase_orders';

    public static function generateReference(): string
    {
        return 'PO-' . date('Ym') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    /** @return array<int, array<string, mixed>> */
    public static function withSupplier(): array
    {
        return static::db()->select(
            'SELECT po.*, s.name AS supplier_name
             FROM purchase_orders po JOIN suppliers s ON s.id = po.supplier_id
             ORDER BY po.created_at DESC',
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function items(int $purchaseOrderId): array
    {
        return static::db()->select(
            'SELECT poi.*, v.sku, v.label AS variant_label, p.name AS product_name
             FROM purchase_order_items poi
             JOIN product_variants v ON v.id = poi.variant_id
             JOIN products p ON p.id = v.product_id
             WHERE poi.purchase_order_id = :id
             ORDER BY poi.id ASC',
            ['id' => $purchaseOrderId],
        );
    }
}
