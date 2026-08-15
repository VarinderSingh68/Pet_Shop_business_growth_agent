<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $po */
/** @var array|null $supplier */
/** @var array $items */
/** @var int $totalCostPaise */

$statusColors = ['draft' => 'badge-neutral', 'ordered' => 'badge-info', 'received' => 'badge-success', 'cancelled' => 'badge-danger'];
?>

<a href="/admin/inventory/purchase-orders" class="text-sm text-ink/50 hover:text-leash">&larr; Purchase orders</a>

<div class="mt-2 flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="font-display text-xl font-bold"><?= e($po['reference']) ?></h1>
    <p class="text-sm text-ink/50 mt-1"><?= e($supplier['name'] ?? '—') ?><?= $po['expected_at'] ? ' · expected ' . e(date('d M Y', strtotime((string) $po['expected_at']))) : '' ?></p>
  </div>
  <span class="badge <?= $statusColors[$po['status']] ?? 'badge-neutral' ?> capitalize text-sm"><?= e($po['status']) ?></span>
</div>

<?php if ($po['notes']): ?><p class="mt-3 text-sm text-ink/70 border-2 border-mist bg-white p-3"><?= e($po['notes']) ?></p><?php endif; ?>

<div class="mt-6 grid lg:grid-cols-[1fr_280px] gap-6">
  <div class="space-y-4">
    <div class="border-2 border-ink bg-white overflow-x-auto">
      <table class="admin-table w-full text-sm">
        <thead>
          <tr class="border-b-2 border-ink">
            <th class="p-2 text-left">Product</th><th class="p-2 text-left">SKU</th><th class="p-2 text-right">Ordered</th>
            <th class="p-2 text-right">Received</th><th class="p-2 text-right">Unit cost</th><th class="p-2"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $i): ?>
            <?php $remaining = (int) $i['quantity'] - (int) $i['received_quantity']; ?>
            <tr class="border-b border-mist">
              <td class="p-2"><?= e($i['product_name']) ?> <span class="text-ink/50">(<?= e($i['variant_label']) ?>)</span></td>
              <td class="p-2 text-ink/60"><?= e($i['sku']) ?></td>
              <td class="p-2 text-right"><?= (int) $i['quantity'] ?></td>
              <td class="p-2 text-right <?= $remaining > 0 ? 'text-leash font-semibold' : 'text-fern font-semibold' ?>"><?= (int) $i['received_quantity'] ?></td>
              <td class="p-2 text-right"><?= money((int) $i['unit_cost_paise']) ?></td>
              <td class="p-2 whitespace-nowrap">
                <?php if ($remaining > 0 && $po['status'] !== 'cancelled'): ?>
                  <form method="POST" action="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>/items/<?= (int) $i['id'] ?>/receive" class="flex items-center gap-1">
                    <?= csrf_field() ?>
                    <input type="number" name="quantity" min="1" max="<?= $remaining ?>" value="<?= $remaining ?>" class="w-16 border border-ink px-1.5 py-1 text-sm">
                    <button type="submit" class="btn btn-secondary btn-sm">Receive</button>
                  </form>
                <?php elseif ($po['status'] === 'draft'): ?>
                  <form method="POST" action="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>/items/<?= (int) $i['id'] ?>/delete" onsubmit="return confirm('Remove this line item?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs font-semibold text-leash hover:underline">Remove</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($items === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No line items yet.</td></tr><?php endif; ?>
        </tbody>
        <?php if ($items !== []): ?>
          <tfoot><tr class="border-t-2 border-ink font-semibold"><td colspan="4" class="p-2 text-right">Total cost</td><td class="p-2 text-right"><?= money($totalCostPaise) ?></td><td></td></tr></tfoot>
        <?php endif; ?>
      </table>
    </div>

    <?php if ($po['status'] === 'draft'): ?>
      <div class="card-tag p-5 bg-white">
        <p class="font-display font-semibold mb-3">Add line item</p>
        <form method="POST" action="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>/items" class="flex flex-wrap items-end gap-2">
          <?= csrf_field() ?>
          <div>
            <label for="sku" class="block text-xs font-semibold mb-1">Variant SKU</label>
            <input id="sku" type="text" name="sku" required placeholder="e.g. CHICKE-1-12" class="input text-sm">
          </div>
          <div>
            <label for="quantity" class="block text-xs font-semibold mb-1">Quantity</label>
            <input id="quantity" type="number" name="quantity" min="1" required class="input text-sm w-24">
          </div>
          <div>
            <label for="unit_cost" class="block text-xs font-semibold mb-1">Unit cost (₹)</label>
            <input id="unit_cost" type="number" name="unit_cost" step="0.01" min="0" required class="input text-sm w-28">
          </div>
          <button type="submit" class="btn btn-secondary btn-sm">Add</button>
        </form>
        <p class="mt-2 text-xs text-ink/50">Find SKUs on the <a href="/admin/inventory" class="underline">Stock</a> page.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold mb-3">Status</p>
    <?php if ($po['status'] === 'draft'): ?>
      <form method="POST" action="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>/status" class="space-y-2">
        <?= csrf_field() ?><input type="hidden" name="status" value="ordered">
        <button type="submit" class="w-full btn btn-primary btn-sm" <?= $items === [] ? 'disabled' : '' ?>>Mark as ordered</button>
      </form>
    <?php endif; ?>
    <?php if (in_array($po['status'], ['draft', 'ordered'], true)): ?>
      <form method="POST" action="/admin/inventory/purchase-orders/<?= (int) $po['id'] ?>/status" class="mt-2" onsubmit="return confirm('Cancel this purchase order?');">
        <?= csrf_field() ?><input type="hidden" name="status" value="cancelled">
        <button type="submit" class="w-full btn btn-ghost btn-sm text-leash">Cancel order</button>
      </form>
    <?php endif; ?>
    <?php if ($po['status'] === 'received'): ?>
      <p class="text-sm text-fern font-semibold">Fully received.</p>
    <?php endif; ?>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
