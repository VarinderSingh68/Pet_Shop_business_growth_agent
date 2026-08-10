<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var string $from */
/** @var string $to */
/** @var array $coupons */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/reports/sales" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Sales</a>
  <a href="/admin/reports/coupons" class="text-sm font-semibold border-b-2 border-ink pb-1">Coupon cost</a>
  <a href="/admin/reports/cohorts" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Cohorts & retention</a>
  <a href="/admin/reports/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Service utilisation</a>
  <a href="/admin/reports/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Inventory turnover</a>
</div>

<form method="GET" action="/admin/reports/coupons" class="flex flex-wrap items-end gap-2 mb-4">
  <input type="date" name="from" value="<?= e($from) ?>" class="input text-sm">
  <input type="date" name="to" value="<?= e($to) ?>" class="input text-sm">
  <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  <a href="/admin/reports/coupons/export?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-secondary btn-sm">Export CSV</a>
</form>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Code</th><th class="p-2 text-right">Redemptions</th><th class="p-2 text-right">Total discount</th></tr></thead>
    <tbody>
      <?php foreach ($coupons as $c): ?>
        <tr class="border-b border-mist"><td class="p-2 font-mono"><?= e($c['code']) ?></td><td class="p-2 text-right"><?= (int) $c['redemptions'] ?></td><td class="p-2 text-right font-semibold text-leash"><?= money((int) $c['total_discount_paise']) ?></td></tr>
      <?php endforeach; ?>
      <?php if ($coupons === []): ?><tr><td colspan="3" class="p-6 text-center text-ink/50">No coupon redemptions in range.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
