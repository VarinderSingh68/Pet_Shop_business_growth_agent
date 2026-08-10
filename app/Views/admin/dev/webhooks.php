<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $deliveries */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Webhooks</h1>

<div class="mt-4 space-y-3" x-data="{ open: null }">
  <?php foreach ($deliveries as $i => $d): ?>
    <div class="border-2 <?= $d['outcome'] === 'failed' ? 'border-leash' : 'border-ink' ?> bg-white p-4 text-sm">
      <div class="flex items-center justify-between gap-3">
        <div class="cursor-pointer" @click="open = open === <?= $i ?> ? null : <?= $i ?>">
          <span class="font-semibold"><?= e($d['source']) ?></span> — <?= e($d['event_type'] ?? 'unknown event') ?>
          <span class="ml-2 text-xs font-semibold <?= $d['signature_valid'] ? 'text-fern' : 'text-leash' ?>"><?= $d['signature_valid'] ? 'Signature valid' : 'Signature invalid' ?></span>
          <span class="ml-2 text-xs text-ink/40"><?= e($d['created_at']) ?></span>
        </div>
        <form method="POST" action="/admin/dev/webhooks/<?= (int) $d['id'] ?>/replay">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary btn-sm">Replay</button>
        </form>
      </div>
      <?php if ($d['error']): ?><p class="mt-2 text-xs text-leash"><?= e($d['error']) ?></p><?php endif; ?>
      <pre x-show="open === <?= $i ?>" x-cloak class="mt-2 bg-mist/40 p-3 text-xs overflow-x-auto"><?= e($d['payload']) ?></pre>
    </div>
  <?php endforeach; ?>
  <?php if ($deliveries === []): ?><p class="text-ink/50 text-sm">No webhook deliveries yet.</p><?php endif; ?>
</div>

<?php \App\Core\View::stop(); ?>
