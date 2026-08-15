<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $purchaseOrders */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */

$statusColors = ['draft' => 'badge-neutral', 'ordered' => 'badge-info', 'received' => 'badge-success', 'cancelled' => 'badge-danger'];
$totalPages = max(1, (int) ceil($total / $perPage));
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-4">
  <div class="flex gap-2">
    <a href="/admin/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Stock</a>
    <a href="/admin/inventory/suppliers" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Suppliers</a>
    <a href="/admin/inventory/purchase-orders" class="text-sm font-semibold border-b-2 border-ink pb-1">Purchase orders</a>
  </div>
  <a href="/admin/inventory/purchase-orders/create" class="btn btn-primary btn-sm">New purchase order</a>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Reference</th><th class="p-2 text-left">Supplier</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Expected</th><th class="p-2 text-left">Created</th></tr></thead>
    <tbody>
      <?php foreach ($purchaseOrders as $po): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-mono font-medium"><a href="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>" class="hover:text-leash"><?= e($po['reference']) ?></a></td>
          <td class="p-2"><?= e($po['supplier_name']) ?></td>
          <td class="p-2"><span class="badge <?= $statusColors[$po['status']] ?? 'badge-neutral' ?> capitalize"><?= e($po['status']) ?></span></td>
          <td class="p-2 text-ink/50"><?= $po['expected_at'] ? e(date('d M Y', strtotime((string) $po['expected_at']))) : '—' ?></td>
          <td class="p-2 text-ink/50"><?= e(date('d M Y', strtotime((string) $po['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($purchaseOrders === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">No purchase orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::include('components/admin-pagination', ['page' => $page, 'totalPages' => $totalPages, 'basePath' => '/admin/inventory/purchase-orders']); ?>

<?php \App\Core\View::stop(); ?>
