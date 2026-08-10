<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $queries */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Query profiler</h1>
<p class="text-sm text-ink/60">Queries slower than 50ms, most recent first.</p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Query</th><th class="p-2 text-right">Duration</th><th class="p-2 text-left">Path</th><th class="p-2 text-left">When</th></tr></thead>
    <tbody>
      <?php foreach ($queries as $q): ?>
        <tr class="border-b border-mist align-top">
          <td class="p-2 font-mono text-xs max-w-md">
            <?= e($q['sql_text']) ?>
            <?php if ($q['bindings'] && $q['bindings'] !== '[]' && $q['bindings'] !== 'null'): ?>
              <p class="text-ink/40 mt-1"><?= e($q['bindings']) ?></p>
            <?php endif; ?>
          </td>
          <td class="p-2 text-right font-semibold <?= (float) $q['duration_ms'] > 200 ? 'text-leash' : '' ?>"><?= e($q['duration_ms']) ?>ms</td>
          <td class="p-2 text-ink/50"><?= e($q['request_path'] ?? '—') ?></td>
          <td class="p-2 text-ink/40 whitespace-nowrap"><?= e($q['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($queries === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">No slow queries recorded — good sign.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
