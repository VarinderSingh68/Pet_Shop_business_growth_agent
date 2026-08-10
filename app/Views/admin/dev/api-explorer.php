<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $routes */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<div class="mt-2 flex items-center justify-between">
  <h1 class="font-display text-xl font-semibold">API explorer</h1>
  <a href="/admin/dev/api-tokens" class="text-sm font-semibold text-leash hover:underline">Manage tokens &rarr;</a>
</div>

<div class="mt-4 space-y-3" x-data="{ tryOpen: null, result: '' }">
  <?php foreach ($routes as $i => $r): ?>
    <div class="card-tag p-4 bg-white">
      <div class="flex items-center gap-3 flex-wrap">
        <span class="text-xs font-bold px-2 py-1 <?= $r['method'] === 'GET' ? 'bg-fern text-paper' : 'bg-leash text-paper' ?>"><?= e($r['method']) ?></span>
        <code class="text-sm font-mono"><?= e($r['uri']) ?></code>
        <span class="text-xs text-ink/40">&rarr; <?= e($r['handler']) ?></span>
        <?php if (in_array('api_token', $r['middleware'], true)): ?>
          <span class="text-xs font-semibold text-tennis border-2 border-tennis px-2 py-0.5">Requires token</span>
        <?php endif; ?>
      </div>
      <?php if ($r['method'] === 'GET'): ?>
        <button type="button" @click="tryOpen = tryOpen === <?= $i ?> ? null : <?= $i ?>; result = ''" class="mt-2 text-xs font-semibold text-leash hover:underline">Try it</button>
        <div x-show="tryOpen === <?= $i ?>" x-cloak class="mt-2">
          <div class="flex gap-2">
            <input type="text" x-ref="token<?= $i ?>" placeholder="Bearer token (if required)" class="flex-1 text-xs input">
            <button type="button" class="btn btn-secondary btn-sm"
                    @click="fetch('<?= e($r['uri']) ?>'.replace(/\{[^}]+\}/g, '1'), { headers: $refs.token<?= $i ?>.value ? { Authorization: 'Bearer ' + $refs.token<?= $i ?>.value } : {} }).then(r => r.text()).then(t => result = t)">
              Send
            </button>
          </div>
          <pre class="mt-2 bg-mist/40 p-3 text-xs overflow-x-auto" x-show="tryOpen === <?= $i ?> && result" x-text="result"></pre>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php \App\Core\View::stop(); ?>
