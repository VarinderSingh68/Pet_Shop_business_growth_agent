<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $logs */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Mail log</h1>
<p class="text-sm text-ink/60">Every email the app has sent (or logged, in catch-all mode when SMTP isn't configured).</p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">To</th><th class="p-2 text-left">Subject</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">When</th><th class="p-2"></th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr class="border-b border-mist">
          <td class="p-2"><?= e($l['to_name']) ?> &lt;<?= e($l['to_email']) ?>&gt;</td>
          <td class="p-2"><?= e($l['subject']) ?></td>
          <td class="p-2"><span class="text-xs font-semibold <?= $l['status'] === 'failed' ? 'text-leash' : 'text-fern' ?>"><?= e(ucfirst($l['status'])) ?></span></td>
          <td class="p-2 text-ink/50"><?= e($l['created_at']) ?></td>
          <td class="p-2"><a href="/admin/dev/mail/<?= (int) $l['id'] ?>/preview" target="_blank" class="text-xs font-semibold text-leash hover:underline">Preview</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($logs === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">No mail sent yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
