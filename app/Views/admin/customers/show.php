<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $customer */
/** @var array $orders */
/** @var array $pets */
/** @var array $addresses */
/** @var int $lifetimeValue */
/** @var array $activity */
/** @var int $loyaltyBalance */
/** @var array $loyaltyLedger */
?>

<a href="/admin/customers" class="text-sm text-ink/50 hover:text-leash">&larr; Customers</a>

<div class="mt-2 flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="font-display text-2xl font-semibold"><?= e($customer['name']) ?></h1>
    <p class="text-sm text-ink/60"><?= e($customer['email']) ?> <?= $customer['phone'] ? '· ' . e($customer['phone']) : '' ?></p>
  </div>
  <form method="POST" action="/admin/customers/<?= (int) $customer['id'] ?>/impersonate"
        onsubmit="return confirm('Sign in as this customer for support purposes? This is logged.');">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-secondary btn-sm">View as customer</button>
  </form>
</div>

<div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Orders</p><p class="font-display text-2xl font-bold mt-1"><?= count($orders) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Lifetime value</p><p class="font-display text-2xl font-bold mt-1"><?= money($lifetimeValue) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Pets</p><p class="font-display text-2xl font-bold mt-1"><?= count($pets) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Loyalty points</p><p class="font-display text-2xl font-bold mt-1"><?= $loyaltyBalance ?></p></div>
</div>

<div class="mt-6 grid lg:grid-cols-2 gap-6">
  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Orders</p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($orders as $o): ?>
        <li class="flex justify-between border-b border-mist pb-2">
          <a href="/admin/orders/<?= (int) $o['id'] ?>" class="hover:text-leash"><?= e($o['order_number']) ?></a>
          <span class="capitalize text-ink/50"><?= e(str_replace('_', ' ', $o['status'])) ?></span>
          <span class="font-semibold"><?= money((int) $o['total_paise']) ?></span>
        </li>
      <?php endforeach; ?>
      <?php if ($orders === []): ?><li class="text-ink/50">No orders yet.</li><?php endif; ?>
    </ul>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Pets</p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($pets as $p): ?>
        <li class="border-b border-mist pb-2">
          <span class="font-medium"><?= e($p['name']) ?></span> — <?= e(ucfirst($p['species'])) ?>
          <?php if ($p['allergies']): ?><span class="text-leash text-xs"> · allergic: <?= e($p['allergies']) ?></span><?php endif; ?>
        </li>
      <?php endforeach; ?>
      <?php if ($pets === []): ?><li class="text-ink/50">No pets on file.</li><?php endif; ?>
    </ul>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Addresses</p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($addresses as $a): ?>
        <li class="border-b border-mist pb-2"><?= e($a['line1']) ?>, <?= e($a['city']) ?>, <?= e($a['state']) ?> <?= e($a['postal_code']) ?></li>
      <?php endforeach; ?>
      <?php if ($addresses === []): ?><li class="text-ink/50">No saved addresses.</li><?php endif; ?>
    </ul>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Loyalty points — balance: <?= $loyaltyBalance ?></p>
    <ul class="mt-3 space-y-2 text-sm max-h-48 overflow-y-auto">
      <?php foreach ($loyaltyLedger as $l): ?>
        <li class="flex justify-between border-b border-mist pb-2">
          <span><?= e($l['note'] ?? ucfirst($l['type'])) ?></span>
          <span class="font-semibold <?= $l['points'] < 0 ? 'text-leash' : 'text-fern' ?>"><?= $l['points'] > 0 ? '+' : '' ?><?= (int) $l['points'] ?></span>
        </li>
      <?php endforeach; ?>
      <?php if ($loyaltyLedger === []): ?><li class="text-ink/50">No points activity yet.</li><?php endif; ?>
    </ul>
    <form method="POST" action="/admin/customers/<?= (int) $customer['id'] ?>/loyalty" class="mt-4 flex flex-wrap items-end gap-2">
      <?= csrf_field() ?>
      <div>
        <label for="points" class="block text-xs font-semibold mb-1">Adjust by</label>
        <input id="points" type="number" name="points" placeholder="±points" required class="w-24 input text-sm">
      </div>
      <input type="text" name="note" placeholder="Reason (optional)" class="flex-1 min-w-[140px] input text-sm">
      <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </form>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Activity</p>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach ($activity as $a): ?>
        <li class="border-b border-mist pb-2 flex justify-between"><span><?= e($a['description'] ?? $a['action']) ?></span><span class="text-ink/40 text-xs"><?= e($a['created_at']) ?></span></li>
      <?php endforeach; ?>
      <?php if ($activity === []): ?><li class="text-ink/50">No activity recorded.</li><?php endif; ?>
    </ul>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
