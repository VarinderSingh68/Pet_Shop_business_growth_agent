<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $files */
/** @var string $selected */
/** @var string $level */
/** @var string $search */
/** @var array $entries */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Logs</h1>

<form method="GET" action="/admin/dev/logs" class="mt-4 flex flex-wrap gap-2">
  <select name="file" onchange="this.form.submit()" class="input text-sm">
    <?php foreach ($files as $f): ?>
      <option value="<?= e($f) ?>" <?= $selected === $f ? 'selected' : '' ?>><?= e($f) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="level" class="input text-sm">
    <option value="">All levels</option>
    <?php foreach (['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'] as $l): ?>
      <option value="<?= $l ?>" <?= $level === $l ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search message" class="min-w-[200px] input text-sm">
  <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
</form>

<div class="mt-4 space-y-2" x-data="{ open: null }">
  <?php foreach ($entries as $i => $e): ?>
    <div class="border-2 <?= in_array($e['level'], ['ERROR', 'CRITICAL'], true) ? 'border-leash' : 'border-ink' ?> bg-white p-3 text-sm">
      <div class="flex items-center justify-between gap-3 cursor-pointer" @click="open = open === <?= $i ?> ? null : <?= $i ?>">
        <div class="min-w-0">
          <span class="text-xs font-semibold px-2 py-0.5 <?= in_array($e['level'], ['ERROR', 'CRITICAL'], true) ? 'bg-leash text-paper' : 'bg-mist' ?>"><?= e($e['level']) ?></span>
          <span class="ml-2 font-medium"><?= e($e['message']) ?></span>
        </div>
        <span class="text-xs text-ink/40 shrink-0"><?= e($e['timestamp']) ?></span>
      </div>
      <?php if ($e['context']): ?>
        <pre x-show="open === <?= $i ?>" x-cloak class="mt-2 bg-mist/40 p-3 text-xs overflow-x-auto"><?= e(json_encode($e['context'], JSON_PRETTY_PRINT)) ?></pre>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if ($entries === []): ?><p class="text-ink/50 text-sm">No log entries match.</p><?php endif; ?>
</div>

<?php \App\Core\View::stop(); ?>
