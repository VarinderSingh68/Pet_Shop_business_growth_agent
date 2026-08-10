<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $checks */

$statusColors = ['ok' => 'text-fern', 'warn' => 'text-tennis', 'fail' => 'text-leash'];
$statusIcons = ['ok' => '✓', 'warn' => '!', 'fail' => '✕'];
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Health check</h1>

<div class="mt-4 max-w-2xl space-y-2">
  <?php foreach ($checks as $c): ?>
    <div class="card-tag p-4 bg-white flex items-center justify-between">
      <p class="font-medium text-sm"><?= e($c['label']) ?></p>
      <div class="flex items-center gap-2">
        <span class="text-sm text-ink/60"><?= e($c['detail']) ?></span>
        <span class="font-bold <?= $statusColors[$c['status']] ?>"><?= $statusIcons[$c['status']] ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (config('developer_tools') && config('app.debug')): ?>
  <form method="POST" action="/admin/dev/reset-demo-data" class="mt-8" onsubmit="return confirm('This drops every table and reseeds fresh demo data. Only use this in a local/dev environment. Continue?');">
    <?= csrf_field() ?>
    <button type="submit" class="border-2 border-leash text-leash font-semibold px-4 py-2 text-sm hover:bg-leash hover:text-paper">Reset demo data</button>
    <p class="text-xs text-ink/50 mt-1">Only available with APP_DEBUG=true — drops and reseeds the entire database.</p>
  </form>
<?php endif; ?>

<?php \App\Core\View::stop(); ?>
