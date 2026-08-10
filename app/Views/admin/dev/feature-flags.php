<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $flags */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Feature flags</h1>

<div class="mt-4 max-w-xl space-y-2">
  <?php foreach ($flags as $f): ?>
    <div class="card-tag p-4 bg-white flex items-center justify-between">
      <div>
        <p class="font-medium text-sm"><?= e($f['key']) ?></p>
        <?php if ($f['description']): ?><p class="text-xs text-ink/50"><?= e($f['description']) ?></p><?php endif; ?>
      </div>
      <form method="POST" action="/admin/dev/feature-flags/<?= (int) $f['id'] ?>/toggle">
        <?= csrf_field() ?>
        <button type="submit" class="text-xs font-semibold px-3 py-1.5 border-2 <?= $f['is_enabled'] ? 'border-fern text-fern' : 'border-ink text-ink/50' ?>">
          <?= $f['is_enabled'] ? 'Enabled' : 'Disabled' ?>
        </button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if ($flags === []): ?><p class="text-ink/50 text-sm">No feature flags yet.</p><?php endif; ?>

  <form method="POST" action="/admin/dev/feature-flags" class="card-tag p-4 bg-white flex gap-2 mt-4">
    <?= csrf_field() ?>
    <input type="text" name="key" placeholder="flag_key" required class="flex-1 input text-sm">
    <input type="text" name="description" placeholder="Description" class="flex-1 input text-sm">
    <button type="submit" class="btn btn-secondary btn-sm">Add</button>
  </form>
</div>

<?php \App\Core\View::stop(); ?>
