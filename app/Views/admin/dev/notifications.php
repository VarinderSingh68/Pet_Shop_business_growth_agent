<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $notifications */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Notifications</h1>
<p class="text-sm text-ink/60">System and order notifications generated for customers — useful for tracing a support case.</p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">User</th><th class="p-2 text-left">Type</th><th class="p-2 text-left">Channel</th><th class="p-2 text-left">Subject</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">When</th></tr></thead>
    <tbody>
      <?php foreach ($notifications as $n): ?>
        <tr class="border-b border-mist">
          <td class="p-2"><?= e($n['user_name'] ?? '—') ?></td>
          <td class="p-2 font-mono text-xs"><?= e($n['type']) ?></td>
          <td class="p-2 capitalize"><?= e($n['channel']) ?></td>
          <td class="p-2"><?= e($n['subject'] ?? '—') ?></td>
          <td class="p-2"><span class="text-xs font-semibold <?= $n['status'] === 'failed' ? 'text-leash' : ($n['status'] === 'sent' ? 'text-fern' : 'text-ink/50') ?>"><?= e(ucfirst($n['status'])) ?></span></td>
          <td class="p-2 text-ink/50"><?= e($n['sent_at'] ?? $n['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($notifications === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No notifications yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
