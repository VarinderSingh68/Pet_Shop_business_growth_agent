<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var string $from */
/** @var string $to */
/** @var array $summary */
/** @var array $byDay */
/** @var array $byProduct */
/** @var array $byCategory */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/reports/sales" class="text-sm font-semibold border-b-2 border-ink pb-1">Sales</a>
  <a href="/admin/reports/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupon cost</a>
  <a href="/admin/reports/cohorts" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Cohorts & retention</a>
  <a href="/admin/reports/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Service utilisation</a>
  <a href="/admin/reports/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Inventory turnover</a>
</div>

<form method="GET" action="/admin/reports/sales" class="flex flex-wrap items-end gap-2 mb-4">
  <div>
    <label for="from" class="block text-xs font-semibold mb-1">From</label>
    <input id="from" type="date" name="from" value="<?= e($from) ?>" class="input text-sm">
  </div>
  <div>
    <label for="to" class="block text-xs font-semibold mb-1">To</label>
    <input id="to" type="date" name="to" value="<?= e($to) ?>" class="input text-sm">
  </div>
  <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  <a href="/admin/reports/sales/export/csv?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-secondary btn-sm">Export CSV</a>
  <a href="/admin/reports/sales/export/pdf?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-secondary btn-sm">Export PDF</a>
</form>

<div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Orders</p><p class="font-display text-2xl font-bold mt-1"><?= $summary['order_count'] ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Subtotal</p><p class="font-display text-2xl font-bold mt-1"><?= money($summary['subtotal_paise']) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Tax collected</p><p class="font-display text-2xl font-bold mt-1"><?= money($summary['tax_paise']) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Discounts given</p><p class="font-display text-2xl font-bold mt-1"><?= money($summary['discount_paise']) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Total revenue</p><p class="font-display text-2xl font-bold mt-1"><?= money($summary['total_paise']) ?></p></div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <div>
    <p class="font-display font-semibold mb-2">Sales by day</p>
    <div class="border-2 border-ink bg-white overflow-x-auto max-h-96 overflow-y-auto">
      <table class="admin-table w-full text-sm">
        <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Date</th><th class="p-2 text-right">Orders</th><th class="p-2 text-right">Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($byDay as $d): ?>
            <tr class="border-b border-mist"><td class="p-2"><?= e($d['date']) ?></td><td class="p-2 text-right"><?= (int) $d['order_count'] ?></td><td class="p-2 text-right font-semibold"><?= money((int) $d['revenue_paise']) ?></td></tr>
          <?php endforeach; ?>
          <?php if ($byDay === []): ?><tr><td colspan="3" class="p-4 text-center text-ink/50">No orders in range.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <p class="font-display font-semibold mb-2">Top products</p>
    <div class="border-2 border-ink bg-white overflow-x-auto max-h-96 overflow-y-auto">
      <table class="admin-table w-full text-sm">
        <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Product</th><th class="p-2 text-right">Units</th><th class="p-2 text-right">Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($byProduct as $p): ?>
            <tr class="border-b border-mist"><td class="p-2"><?= e($p['product_name']) ?></td><td class="p-2 text-right"><?= (int) $p['units_sold'] ?></td><td class="p-2 text-right font-semibold"><?= money((int) $p['revenue_paise']) ?></td></tr>
          <?php endforeach; ?>
          <?php if ($byProduct === []): ?><tr><td colspan="3" class="p-4 text-center text-ink/50">No sales in range.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="mt-6">
  <p class="font-display font-semibold mb-2">Sales by category</p>
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Category</th><th class="p-2 text-right">Units</th><th class="p-2 text-right">Revenue</th></tr></thead>
      <tbody>
        <?php foreach ($byCategory as $c): ?>
          <tr class="border-b border-mist"><td class="p-2"><?= e($c['category_name']) ?></td><td class="p-2 text-right"><?= (int) $c['units_sold'] ?></td><td class="p-2 text-right font-semibold"><?= money((int) $c['revenue_paise']) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($byCategory === []): ?><tr><td colspan="3" class="p-4 text-center text-ink/50">No sales in range.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
