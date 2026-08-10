<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $tokens */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">API tokens</h1>

<div class="grid lg:grid-cols-[1fr_320px] gap-6 mt-4">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Owner</th><th class="p-2 text-left">Token</th><th class="p-2 text-right">Rate limit</th><th class="p-2 text-left">Last used</th><th class="p-2"></th></tr></thead>
      <tbody>
        <?php foreach ($tokens as $t): ?>
          <tr class="border-b border-mist <?= $t['revoked_at'] ? 'opacity-40' : '' ?>">
            <td class="p-2 font-medium"><?= e($t['name']) ?></td>
            <td class="p-2"><?= e($t['user_name']) ?></td>
            <td class="p-2 font-mono text-xs">pst_&hellip;<?= e($t['last_four']) ?></td>
            <td class="p-2 text-right"><?= (int) $t['rate_limit_per_minute'] ?>/min</td>
            <td class="p-2 text-ink/50"><?= e($t['last_used_at'] ?? 'Never') ?></td>
            <td class="p-2">
              <?php if (!$t['revoked_at']): ?>
                <form method="POST" action="/admin/dev/api-tokens/<?= (int) $t['id'] ?>/revoke">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-xs font-semibold text-leash hover:underline">Revoke</button>
                </form>
              <?php else: ?>
                <span class="text-xs text-ink/40">Revoked</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($tokens === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No tokens yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">New token</p>
    <form method="POST" action="/admin/dev/api-tokens" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="e.g. POS sync" required class="input text-sm">
      <input type="number" name="rate_limit" placeholder="Requests/min (default 60)" class="input text-sm">
      <button type="submit" class="w-full btn btn-secondary btn-sm">Create token</button>
    </form>
    <p class="text-xs text-ink/50 mt-2">The full token is shown once, in the confirmation banner, right after creation.</p>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
