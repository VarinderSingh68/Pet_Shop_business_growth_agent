<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var int $migrationCount */
/** @var array|null $lastCron */
/** @var int $slowQueryCount */
/** @var int $failedWebhookCount */

$tools = [
    ['href' => '/admin/dev/migrations', 'label' => 'Migrations', 'desc' => 'Status, run, rollback, fresh + seed'],
    ['href' => '/admin/dev/logs', 'label' => 'Logs', 'desc' => 'Filter by level, search, expand stack traces'],
    ['href' => '/admin/dev/queries', 'label' => 'Query profiler', 'desc' => 'Slowest queries with bindings'],
    ['href' => '/admin/dev/cron', 'label' => 'Cron monitor', 'desc' => 'Growth Agent run history, manual trigger'],
    ['href' => '/admin/dev/mail', 'label' => 'Mail log', 'desc' => 'Every email sent, with HTML preview'],
    ['href' => '/admin/dev/notifications', 'label' => 'Notifications', 'desc' => 'Order/system notifications sent to customers'],
    ['href' => '/admin/dev/webhooks', 'label' => 'Webhooks', 'desc' => 'Delivery log, signature status, replay'],
    ['href' => '/admin/dev/api-explorer', 'label' => 'API explorer', 'desc' => 'Every registered API route'],
    ['href' => '/admin/dev/api-tokens', 'label' => 'API tokens', 'desc' => 'Issue and revoke Bearer tokens'],
    ['href' => '/admin/dev/feature-flags', 'label' => 'Feature flags', 'desc' => 'Toggle in-progress features'],
    ['href' => '/admin/dev/backups', 'label' => 'Backups', 'desc' => 'Create, download, restore'],
    ['href' => '/admin/dev/health', 'label' => 'Health check', 'desc' => 'PHP, extensions, disk, DB, cron freshness'],
];
?>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Migrations run</p><p class="font-display text-2xl font-bold mt-1"><?= $migrationCount ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Last cron run</p><p class="font-display text-lg font-bold mt-1"><?= $lastCron ? e($lastCron['started_at']) : 'Never' ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Slow queries (24h)</p><p class="font-display text-2xl font-bold mt-1 <?= $slowQueryCount > 0 ? 'text-leash' : '' ?>"><?= $slowQueryCount ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Failed webhooks</p><p class="font-display text-2xl font-bold mt-1 <?= $failedWebhookCount > 0 ? 'text-leash' : '' ?>"><?= $failedWebhookCount ?></p></div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach ($tools as $t): ?>
    <a href="<?= e($t['href']) ?>" class="card-tag p-5 bg-white hover:border-leash">
      <p class="font-display font-semibold"><?= e($t['label']) ?></p>
      <p class="text-xs text-ink/50 mt-1"><?= e($t['desc']) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<?php \App\Core\View::stop(); ?>
