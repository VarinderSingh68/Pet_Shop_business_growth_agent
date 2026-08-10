<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $cohorts */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/reports/sales" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Sales</a>
  <a href="/admin/reports/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupon cost</a>
  <a href="/admin/reports/cohorts" class="text-sm font-semibold border-b-2 border-ink pb-1">Cohorts & retention</a>
  <a href="/admin/reports/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Service utilisation</a>
  <a href="/admin/reports/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Inventory turnover</a>
</div>

<div class="flex justify-end mb-4">
  <a href="/admin/reports/cohorts/export" class="btn btn-secondary btn-sm">Export CSV</a>
</div>

<p class="text-sm text-ink/60 mb-4">Each row is a monthly cohort — customers grouped by the month of their first order — with how many of that cohort went on to order again at least once.</p>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Cohort month</th><th class="p-2 text-right">Customers</th><th class="p-2 text-right">Returned (2+ orders)</th><th class="p-2 text-right">Retention rate</th></tr></thead>
    <tbody>
      <?php foreach ($cohorts as $c): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($c['cohort_month']) ?></td>
          <td class="p-2 text-right"><?= (int) $c['customer_count'] ?></td>
          <td class="p-2 text-right"><?= (int) $c['returning_count'] ?></td>
          <td class="p-2 text-right font-semibold"><?= $c['customer_count'] > 0 ? round($c['returning_count'] / $c['customer_count'] * 100, 1) : 0 ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if ($cohorts === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">Not enough order history yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
