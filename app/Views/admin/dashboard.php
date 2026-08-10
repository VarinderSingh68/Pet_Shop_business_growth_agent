<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var int $revenueToday */
/** @var int $revenueWeek */
/** @var int $revenueMonth */
/** @var float|null $revenueDeltaPercent */
/** @var array $ordersByStatus */
/** @var array $lowStock */
/** @var array $todayAppointments */
/** @var int $newCustomers7d */
/** @var array $topProducts */
/** @var array $recentActivity */
/** @var array $funnel */
/** @var array $opportunities */

$statusCounts = array_column($ordersByStatus, 'c', 'status');
?>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="card-tag card-tag--pop stat-tile p-5 bg-white">
    <span class="icon-chip icon-chip-leash"><?= icon('credit-card', 'h-5 w-5') ?></span>
    <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold mt-3">Revenue today</p>
    <p class="stat-tile__value mt-1" data-count-prefix="₹" data-count-decimals="2" data-count-to="<?= $revenueToday / 100 ?>">₹0</p>
  </div>
  <div class="card-tag card-tag--pop stat-tile p-5 bg-white">
    <span class="icon-chip icon-chip-info"><?= icon('chart-bar', 'h-5 w-5') ?></span>
    <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold mt-3">Revenue this week</p>
    <p class="stat-tile__value mt-1" data-count-prefix="₹" data-count-decimals="2" data-count-to="<?= $revenueWeek / 100 ?>">₹0</p>
  </div>
  <div class="card-tag card-tag--pop stat-tile p-5 bg-white">
    <span class="icon-chip icon-chip-fern"><?= icon('gift', 'h-5 w-5') ?></span>
    <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold mt-3">Revenue (30 days)</p>
    <p class="stat-tile__value mt-1" data-count-prefix="₹" data-count-decimals="2" data-count-to="<?= $revenueMonth / 100 ?>">₹0</p>
    <?php if ($revenueDeltaPercent !== null): ?>
      <p class="text-xs mt-1 flex items-center gap-1 <?= $revenueDeltaPercent >= 0 ? 'stat-tile__delta-up' : 'stat-tile__delta-down' ?> font-semibold">
        <?= $revenueDeltaPercent >= 0 ? '▲' : '▼' ?> <?= abs($revenueDeltaPercent) ?>% vs prior 30 days
      </p>
    <?php endif; ?>
    <?php if (array_sum($revenueSparkline) > 0): ?>
      <div class="mt-2 text-fern"><?= sparkline($revenueSparkline, 140, 28) ?></div>
    <?php endif; ?>
  </div>
  <div class="card-tag card-tag--pop stat-tile p-5 bg-white">
    <span class="icon-chip icon-chip-plum"><?= icon('users', 'h-5 w-5') ?></span>
    <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold mt-3">New customers (7d)</p>
    <p class="stat-tile__value mt-1" data-count-to="<?= $newCustomers7d ?>">0</p>
  </div>
</div>

<div class="mt-6 grid lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 card-tag p-5 bg-white">
    <div class="card-tag__tab !bg-tennis">Copilot</div>
    <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
      <span class="icon-chip icon-chip-plum"><?= icon('megaphone', 'h-4 w-4') ?></span>
      Growth Copilot — top opportunities right now
    </p>
    <?php if ($opportunities === []): ?>
      <p class="mt-2 text-sm text-ink/60">
        Nothing ranked yet — the Growth Agent needs a night or two of order history and segment
        data before it has real opportunities to surface. Run <code>php cron.php</code> after
        some orders come in.
      </p>
    <?php else: ?>
      <ul class="mt-3 space-y-3">
        <?php foreach ($opportunities as $opp): ?>
          <li class="flex items-center justify-between gap-4 border-b border-mist pb-3 last:border-0">
            <div>
              <p class="text-sm font-semibold"><?= e($opp['title']) ?> — <?= e($opp['description']) ?></p>
              <p class="text-xs text-ink/50">Est. revenue <?= money($opp['estimated_impact_paise']) ?> if this converts like similar campaigns.</p>
            </div>
            <form method="POST" action="/admin/marketing/copilot/<?= e($opp['key']) ?>" onsubmit="return confirm('Send this to all ' + <?= (int) $opp['affected_count'] ?> + ' matching customers now?');">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-primary shrink-0">Do it</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <a href="/admin/marketing/activity" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline">View all Growth Agent activity <?= icon('arrow-right', 'h-3 w-3') ?></a>
  </div>
  <div class="card-tag p-5 bg-white">
    <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
      <span class="icon-chip icon-chip-info"><?= icon('cart', 'h-4 w-4') ?></span>
      Orders by status
    </p>
    <?php
      $statusBadge = ['pending_payment' => 'badge-warning', 'confirmed' => 'badge-info', 'processing' => 'badge-info', 'shipped' => 'badge-info', 'delivered' => 'badge-success', 'cancelled' => 'badge-danger'];
    ?>
    <ul class="mt-3 space-y-2.5 text-sm">
      <?php foreach (['pending_payment', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
        <li class="flex justify-between items-center">
          <span class="capitalize"><?= str_replace('_', ' ', $s) ?></span>
          <span class="badge <?= $statusBadge[$s] ?>"><?= (int) ($statusCounts[$s] ?? 0) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="mt-6 card-tag p-5 bg-white">
  <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
    <span class="icon-chip icon-chip-sky"><?= icon('chart-bar', 'h-4 w-4') ?></span>
    Cart-to-order conversion (30 days)
  </p>
  <div class="mt-4 flex items-center gap-6 text-sm">
    <div><p class="text-ink/50">Carts started</p><p class="stat-tile__value" data-count-to="<?= $funnel['carts'] ?>">0</p></div>
    <div class="text-ink/30"><?= icon('arrow-right', 'h-4 w-4') ?></div>
    <div><p class="text-ink/50">Orders placed</p><p class="stat-tile__value" data-count-to="<?= $funnel['orders'] ?>">0</p></div>
    <div class="text-ink/30">=</div>
    <div>
      <p class="text-ink/50">Conversion rate</p>
      <p class="stat-tile__value text-fern" data-count-to="<?= $funnel['carts'] > 0 ? round(($funnel['orders'] / $funnel['carts']) * 100, 1) : 0 ?>" data-count-decimals="1" data-count-suffix="%">0%</p>
    </div>
  </div>
</div>

<div class="mt-6 grid lg:grid-cols-3 gap-4">
  <div class="card-tag p-5 bg-white">
    <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
      <span class="icon-chip icon-chip-tennis"><?= icon('alert-triangle', 'h-4 w-4') ?></span>
      Low stock
    </p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($lowStock as $v): ?>
        <li class="flex justify-between items-center border-b border-mist pb-2">
          <a href="/admin/catalogue/products/<?= (int) $v['product_id'] ?>/edit" class="hover:text-leash"><?= e($v['product_name']) ?> (<?= e($v['label']) ?>)</a>
          <span class="badge badge-warning"><?= (int) $v['stock_quantity'] ?> left</span>
        </li>
      <?php endforeach; ?>
      <?php if ($lowStock === []): ?><li class="text-ink/50">Nothing low on stock.</li><?php endif; ?>
    </ul>
    <a href="/admin/inventory?low_stock=1" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline">View all <?= icon('arrow-right', 'h-3 w-3') ?></a>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
      <span class="icon-chip icon-chip-info"><?= icon('calendar', 'h-4 w-4') ?></span>
      Today's appointments
    </p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($todayAppointments as $a): ?>
        <li class="flex justify-between border-b border-mist pb-2">
          <span><?= date('g:i A', strtotime($a['start_at'])) ?> — <?= e($a['service_name']) ?></span>
          <span class="text-ink/50"><?= e($a['staff_name']) ?></span>
        </li>
      <?php endforeach; ?>
      <?php if ($todayAppointments === []): ?><li class="text-ink/50">Nothing booked today.</li><?php endif; ?>
    </ul>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
      <span class="icon-chip icon-chip-fern"><?= icon('star', 'h-4 w-4') ?></span>
      Top products (30d)
    </p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($topProducts as $p): ?>
        <li class="flex justify-between border-b border-mist pb-2">
          <span><?= e($p['name']) ?></span>
          <span class="font-semibold"><?= money((int) $p['revenue_paise']) ?></span>
        </li>
      <?php endforeach; ?>
      <?php if ($topProducts === []): ?><li class="text-ink/50">No sales yet.</li><?php endif; ?>
    </ul>
  </div>
</div>

<div class="mt-6 card-tag p-5 bg-white">
  <p class="flex items-center gap-2.5 font-display text-lg font-semibold">
    <span class="icon-chip !bg-ink !text-paper"><?= icon('clock', 'h-4 w-4') ?></span>
    Recent activity
  </p>
  <ul class="mt-3 space-y-3 text-sm">
    <?php if ($recentActivity === []): ?>
      <li class="text-ink/50">Nothing logged yet.</li>
    <?php endif; ?>
    <?php foreach ($recentActivity as $row): ?>
      <li class="border-b border-mist pb-2 last:border-0 flex justify-between">
        <span><span class="font-semibold"><?= e($row['user_name'] ?? 'System') ?></span> &mdash; <?= e($row['description'] ?? $row['action']) ?></span>
        <span class="text-xs text-ink/40"><?= e($row['created_at']) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<?php \App\Core\View::stop(); ?>
