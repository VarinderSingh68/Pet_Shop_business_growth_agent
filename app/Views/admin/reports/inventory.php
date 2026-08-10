<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $turnover */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/reports/sales" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Sales</a>
  <a href="/admin/reports/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupon cost</a>
  <a href="/admin/reports/cohorts" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Cohorts & retention</a>
  <a href="/admin/reports/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Service utilisation</a>
  <a href="/admin/reports/inventory" class="text-sm font-semibold border-b-2 border-ink pb-1">Inventory turnover</a>
</div>

<div class="flex justify-end mb-4">
  <a href="/admin/reports/inventory/export" class="btn btn-secondary btn-sm">Export CSV</a>
</div>

<p class="text-sm text-ink/60 mb-4">Units sold in the last 30 days against current stock — low units-sold with high stock is a signal to stop reordering; the reverse is a restock signal.</p>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Product</th><th class="p-2 text-left">Variant</th><th class="p-2 text-right">Units sold (30d)</th><th class="p-2 text-right">Current stock</th></tr></thead>
    <tbody>
      <?php foreach ($turnover as $t): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($t['product_name']) ?></td>
          <td class="p-2 text-ink/60"><?= e($t['variant_label']) ?></td>
          <td class="p-2 text-right"><?= (int) $t['units_sold_30d'] ?></td>
          <td class="p-2 text-right <?= (int) $t['current_stock'] === 0 ? 'text-leash font-semibold' : '' ?>"><?= (int) $t['current_stock'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($turnover === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">No variants found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
