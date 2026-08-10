<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $backups */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<div class="mt-2 flex items-center justify-between">
  <h1 class="font-display text-xl font-semibold">Backups</h1>
  <form method="POST" action="/admin/dev/backups/create">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-primary btn-sm">Create backup now</button>
  </form>
</div>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">File</th><th class="p-2 text-right">Size</th><th class="p-2 text-left">Created</th><th class="p-2"></th></tr></thead>
    <tbody>
      <?php foreach ($backups as $b): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-mono text-xs"><?= e($b['name']) ?></td>
          <td class="p-2 text-right"><?= number_format($b['size'] / 1024, 1) ?> KB</td>
          <td class="p-2 text-ink/50"><?= date('d M Y H:i', $b['created_at']) ?></td>
          <td class="p-2 whitespace-nowrap">
            <a href="/admin/dev/backups/<?= e($b['name']) ?>/download" class="text-xs font-semibold text-leash hover:underline">Download</a>
            <form method="POST" action="/admin/dev/backups/<?= e($b['name']) ?>/restore" class="inline" onsubmit="return confirm('This overwrites the current database with this backup. Continue?');">
              <?= csrf_field() ?>
              <button type="submit" class="text-xs font-semibold text-ink/50 hover:text-leash ml-2">Restore</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($backups === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">No backups yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
