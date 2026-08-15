<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\PurchaseOrder;
use App\Models\Supplier;

final class PurchaseOrderController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/inventory/purchase-orders/index', [
            'title' => 'Purchase orders',
            'purchaseOrders' => PurchaseOrder::withSupplier(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->view('admin/inventory/purchase-orders/create', [
            'title' => 'New purchase order',
            'suppliers' => Supplier::where(['is_active' => 1], 'name ASC'),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->only(['supplier_id', 'expected_at', 'notes']);

        $validator = Validator::make($data, ['supplier_id' => 'required|integer']);
        if ($validator->fails()) {
            flash('error', 'Choose a supplier.');
            back();
        }

        $id = PurchaseOrder::create([
            'supplier_id' => (int) $data['supplier_id'],
            'reference' => PurchaseOrder::generateReference(),
            'status' => 'draft',
            'notes' => !empty($data['notes']) ? $data['notes'] : null,
            'expected_at' => !empty($data['expected_at']) ? $data['expected_at'] : null,
            'created_by_user_id' => App::auth()->id(),
        ]);

        flash('success', 'Purchase order created. Add line items below.');
        $this->redirect('/admin/inventory/purchase-orders/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $po = PurchaseOrder::find((int) $id);
        if ($po === null) {
            abort(404);
        }

        $supplier = Supplier::find((int) $po['supplier_id']);
        $items = PurchaseOrder::items((int) $id);

        $totalCostPaise = array_sum(array_map(static fn (array $i) => (int) $i['quantity'] * (int) $i['unit_cost_paise'], $items));

        $this->view('admin/inventory/purchase-orders/show', [
            'title' => 'Purchase order ' . $po['reference'],
            'po' => $po,
            'supplier' => $supplier,
            'items' => $items,
            'totalCostPaise' => $totalCostPaise,
        ]);
    }

    public function storeItem(Request $request, string $id): void
    {
        $po = PurchaseOrder::find((int) $id);
        if ($po === null) {
            abort(404);
        }

        $sku = trim((string) $request->input('sku', ''));
        $quantity = (int) $request->input('quantity', 0);
        $unitCost = (float) $request->input('unit_cost', 0);

        $variant = Database::instance()->selectOne('SELECT * FROM product_variants WHERE sku = :sku', ['sku' => $sku]);
        if ($variant === null) {
            flash('error', "No variant found with SKU \"{$sku}\".");
            back();
        }
        if ($quantity <= 0) {
            flash('error', 'Enter a quantity greater than zero.');
            back();
        }

        Database::instance()->insert('purchase_order_items', [
            'purchase_order_id' => $id,
            'variant_id' => $variant['id'],
            'quantity' => $quantity,
            'received_quantity' => 0,
            'unit_cost_paise' => (int) round($unitCost * 100),
            'created_at' => now(),
        ]);

        flash('success', 'Line item added.');
        $this->redirect('/admin/inventory/purchase-orders/' . $id);
    }

    public function destroyItem(Request $request, string $id, string $itemId): void
    {
        Database::instance()->delete('purchase_order_items', 'id = :id AND purchase_order_id = :pid', ['id' => $itemId, 'pid' => $id]);
        flash('success', 'Line item removed.');
        $this->redirect('/admin/inventory/purchase-orders/' . $id);
    }

    public function updateStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        if (!in_array($status, ['draft', 'ordered', 'cancelled'], true)) {
            back();
        }

        PurchaseOrder::updateWhere((int) $id, ['status' => $status]);
        flash('success', 'Purchase order updated.');
        $this->redirect('/admin/inventory/purchase-orders/' . $id);
    }

    /** Receives stock for one line item: bumps received_quantity, moves stock, logs it. */
    public function receiveItem(Request $request, string $id, string $itemId): void
    {
        $qty = (int) $request->input('quantity', 0);
        if ($qty <= 0) {
            back();
        }

        $db = Database::instance();

        $db->transaction(function (Database $db) use ($id, $itemId, $qty) {
            $item = $db->selectOne('SELECT * FROM purchase_order_items WHERE id = :id AND purchase_order_id = :pid', ['id' => $itemId, 'pid' => $id]);
            if ($item === null) {
                throw new \RuntimeException('Line item not found.');
            }

            $remaining = (int) $item['quantity'] - (int) $item['received_quantity'];
            $receiveQty = min($qty, max(0, $remaining));
            if ($receiveQty <= 0) {
                return;
            }

            $variant = $db->selectOne('SELECT * FROM product_variants WHERE id = :id', ['id' => $item['variant_id']]);
            $newStock = (int) $variant['stock_quantity'] + $receiveQty;

            $db->update('product_variants', ['stock_quantity' => $newStock], 'id = :id', ['id' => $item['variant_id']]);
            $db->update('purchase_order_items', ['received_quantity' => (int) $item['received_quantity'] + $receiveQty], 'id = :id', ['id' => $itemId]);

            $db->insert('inventory_movements', [
                'variant_id' => $item['variant_id'],
                'change_quantity' => $receiveQty,
                'reason' => 'restock',
                'reference_type' => 'purchase_order',
                'reference_id' => $id,
                'note' => 'Received against PO #' . $id,
                'created_by_user_id' => App::auth()->id(),
                'created_at' => now(),
            ]);
        });

        $items = PurchaseOrder::items((int) $id);
        $allReceived = $items !== [] && array_reduce(
            $items,
            static fn (bool $carry, array $i) => $carry && (int) $i['received_quantity'] >= (int) $i['quantity'],
            true,
        );
        if ($allReceived) {
            PurchaseOrder::updateWhere((int) $id, ['status' => 'received']);
        }

        flash('success', 'Stock received.');
        $this->redirect('/admin/inventory/purchase-orders/' . $id);
    }
}
