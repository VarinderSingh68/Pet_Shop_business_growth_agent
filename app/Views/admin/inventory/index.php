<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $variants */
/** @var int $lowStockCount */
/** @var bool $lowStockOnly */
/** @var string $search */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/inventory" class="text-sm font-semibold border-b-2 border-ink pb-1">Stock</a>
  <a href="/admin/inventory/suppliers" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Suppliers</a>
  <a href="/admin/inventory/purchase-orders" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Purchase orders</a>
</div>

<div class="flex items-center justify-between flex-wrap gap-3 mb-4">
  <form method="GET" action="/admin/inventory" class="flex flex-wrap gap-2">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search product or SKU" class="min-w-[220px] input text-sm">
    <label class="flex items-center gap-2 border-2 border-ink px-3 py-1.5 text-sm cursor-pointer">
      <input type="checkbox" name="low_stock" value="1" <?= $lowStockOnly ? 'checked' : '' ?> onchange="this.form.submit()">
      Low stock only (<?= $lowStockCount ?>)
    </label>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
  </form>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">Product</th><th class="p-2 text-left">Variant</th><th class="p-2 text-left">SKU</th>
        <th class="p-2 text-right">Stock</th><th class="p-2 text-right">Low at</th><th class="p-2 text-left">Adjust</th><th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($variants as $v): ?>
        <?php $low = (int) $v['stock_quantity'] <= (int) $v['low_stock_threshold']; ?>
        <tr class="border-b border-mist <?= $low ? 'bg-leash/5' : '' ?>">
          <td class="p-2"><a href="/admin/catalogue/products/<?= (int) $v['product_id'] ?>/edit" class="hover:text-leash font-medium"><?= e($v['product_name']) ?></a></td>
          <td class="p-2"><?= e($v['label']) ?></td>
          <td class="p-2 text-ink/60"><?= e($v['sku']) ?></td>
          <td class="p-2 text-right font-semibold <?= $low ? 'text-leash' : '' ?>"><?= (int) $v['stock_quantity'] ?></td>
          <td class="p-2 text-right text-ink/50"><?= (int) $v['low_stock_threshold'] ?></td>
          <td class="p-2">
            <form method="POST" action="/admin/inventory/<?= (int) $v['id'] ?>/adjust" class="flex items-center gap-1">
              <?= csrf_field() ?>
              <input type="number" name="delta" placeholder="±qty" required class="w-20 border border-ink px-1.5 py-1 text-sm">
              <select name="reason" class="border border-ink px-1 py-1 text-xs">
                <option value="restock">Restock</option>
                <option value="adjustment">Adjustment</option>
                <option value="return">Return</option>
                <option value="stock_take">Stock take</option>
              </select>
              <button type="submit" class="border btn btn-secondary btn-sm">Apply</button>
            </form>
          </td>
          <td class="p-2"><a href="/admin/inventory/<?= (int) $v['id'] ?>/ledger" class="text-xs font-semibold text-leash hover:underline">History</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($variants === []): ?>
        <tr><td colspan="7" class="p-6 text-center text-ink/50">No variants match.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
