<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $runs */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<div class="mt-2 flex items-center justify-between">
  <h1 class="font-display text-xl font-semibold">Cron monitor</h1>
  <form method="POST" action="/admin/dev/cron/run">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-primary btn-sm">Run Growth Agent now</button>
  </form>
</div>
<p class="text-sm text-ink/60 mt-1">Each of the Growth Agent's jobs, tracked separately with duration and outcome.</p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Job</th><th class="p-2 text-left">Started</th><th class="p-2 text-right">Duration</th><th class="p-2 text-left">Outcome</th><th class="p-2 text-left">Summary</th></tr></thead>
    <tbody>
      <?php foreach ($runs as $r): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e(str_replace('_', ' ', $r['job_name'])) ?></td>
          <td class="p-2 text-ink/50"><?= e($r['started_at']) ?></td>
          <td class="p-2 text-right"><?= $r['duration_ms'] !== null ? (int) $r['duration_ms'] . 'ms' : '—' ?></td>
          <td class="p-2">
            <?php if ($r['outcome'] === null): ?>
              <span class="text-xs font-semibold text-tennis">Running…</span>
            <?php else: ?>
              <span class="text-xs font-semibold <?= $r['outcome'] === 'success' ? 'text-fern' : 'text-leash' ?>"><?= ucfirst($r['outcome']) ?></span>
            <?php endif; ?>
          </td>
          <td class="p-2 text-ink/60"><?= e($r['summary'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($runs === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">No cron runs yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
