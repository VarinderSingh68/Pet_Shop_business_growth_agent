<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var string $from */
/** @var string $to */
/** @var array $utilization */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/reports/sales" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Sales</a>
  <a href="/admin/reports/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupon cost</a>
  <a href="/admin/reports/cohorts" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Cohorts & retention</a>
  <a href="/admin/reports/services" class="text-sm font-semibold border-b-2 border-ink pb-1">Service utilisation</a>
  <a href="/admin/reports/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Inventory turnover</a>
</div>

<form method="GET" action="/admin/reports/services" class="flex flex-wrap items-end gap-2 mb-4">
  <input type="date" name="from" value="<?= e($from) ?>" class="input text-sm">
  <input type="date" name="to" value="<?= e($to) ?>" class="input text-sm">
  <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  <a href="/admin/reports/services/export?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-secondary btn-sm">Export CSV</a>
</form>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Service</th><th class="p-2 text-right">Total slots</th><th class="p-2 text-right">Booked</th><th class="p-2 text-right">Utilisation</th></tr></thead>
    <tbody>
      <?php foreach ($utilization as $u): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($u['service_name']) ?></td>
          <td class="p-2 text-right"><?= (int) $u['total_slots'] ?></td>
          <td class="p-2 text-right"><?= (int) $u['booked_slots'] ?></td>
          <td class="p-2 text-right font-semibold <?= (float) $u['utilization_percent'] < 30 ? 'text-leash' : 'text-fern' ?>"><?= e($u['utilization_percent']) ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if ($utilization === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">No slots in range.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
