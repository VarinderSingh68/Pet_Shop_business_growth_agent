<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class InventoryController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::instance();
        $lowStockOnly = (bool) $request->query('low_stock', false);
        $search = trim((string) $request->query('q', ''));

        $where = ['v.deleted_at IS NULL', 'p.deleted_at IS NULL'];
        $bindings = [];
        if ($lowStockOnly) {
            $where[] = 'v.stock_quantity <= v.low_stock_threshold';
        }
        if ($search !== '') {
            $where[] = '(p.name LIKE :q OR v.sku LIKE :q OR v.label LIKE :q)';
            $bindings['q'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);

        $variants = $db->select(
            "SELECT v.*, p.name AS product_name, p.slug AS product_slug
             FROM product_variants v
             JOIN products p ON p.id = v.product_id
             WHERE {$whereSql}
             ORDER BY (v.stock_quantity <= v.low_stock_threshold) DESC, p.name ASC
             LIMIT 200",
            $bindings,
        );

        $lowStockCount = (int) ($db->selectOne(
            'SELECT COUNT(*) c FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.deleted_at IS NULL AND p.deleted_at IS NULL AND v.stock_quantity <= v.low_stock_threshold',
        )['c'] ?? 0);

        $this->view('admin/inventory/index', [
            'title' => 'Inventory',
            'variants' => $variants,
            'lowStockCount' => $lowStockCount,
            'lowStockOnly' => $lowStockOnly,
            'search' => $search,
        ]);
    }

    public function adjust(Request $request, string $variantId): void
    {
        $delta = (int) $request->input('delta', 0);
        $reason = (string) $request->input('reason', 'adjustment');
        $note = (string) $request->input('note', '');

        if ($delta === 0) {
            back();
        }

        $db = Database::instance();

        $db->transaction(function (Database $db) use ($variantId, $delta, $reason, $note) {
            $variant = $db->selectOne('SELECT * FROM product_variants WHERE id = :id FOR UPDATE', ['id' => $variantId]);
            if ($variant === null) {
                throw new \RuntimeException('Variant not found.');
            }

            $newStock = max(0, (int) $variant['stock_quantity'] + $delta);

            $db->update('product_variants', ['stock_quantity' => $newStock], 'id = :id', ['id' => $variantId]);

            $db->insert('inventory_movements', [
                'variant_id' => $variantId,
                'change_quantity' => $newStock - (int) $variant['stock_quantity'],
                'reason' => in_array($reason, ['restock', 'adjustment', 'return', 'stock_take'], true) ? $reason : 'adjustment',
                'note' => $note !== '' ? $note : null,
                'created_by_user_id' => App::auth()->id(),
                'created_at' => now(),
            ]);
        });

        flash('success', 'Stock updated.');
        back();
    }

    public function ledger(Request $request, string $variantId): void
    {
        $db = Database::instance();
        $variant = $db->selectOne(
            'SELECT v.*, p.name AS product_name FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = :id',
            ['id' => $variantId],
        );
        if ($variant === null) {
            abort(404);
        }

        $movements = $db->select(
            'SELECT m.*, u.name AS user_name FROM inventory_movements m
             LEFT JOIN users u ON u.id = m.created_by_user_id
             WHERE m.variant_id = :vid ORDER BY m.created_at DESC LIMIT 100',
            ['vid' => $variantId],
        );

        $this->view('admin/inventory/ledger', [
            'title' => 'Stock ledger — ' . $variant['product_name'],
            'variant' => $variant,
            'movements' => $movements,
        ]);
    }
}
