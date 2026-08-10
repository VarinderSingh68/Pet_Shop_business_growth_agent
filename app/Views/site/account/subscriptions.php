<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $subscriptions */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'subscriptions']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">My subscriptions</h1>
      <p class="mt-2 text-ink/60 text-sm">Set up "deliver every N days" from any food or litter product page.</p>

      <?php if ($subscriptions === []): ?>
        <div class="mt-8 card-tag p-10 text-center">
          <p class="font-semibold">No active subscriptions.</p>
          <a href="/shop" class="mt-4 inline-block btn btn-primary">Browse products</a>
        </div>
      <?php else: ?>
        <div class="mt-6 space-y-3">
          <?php foreach ($subscriptions as $sub): ?>
            <div class="card-tag p-5">
              <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                  <a href="/shop/<?= e($sub['product_slug']) ?>" class="font-semibold hover:text-leash"><?= e($sub['product_name']) ?></a>
                  <p class="text-sm text-ink/60 mt-1"><?= e($sub['variant_label']) ?> &middot; Qty <?= (int) $sub['quantity'] ?> &middot; every <?= (int) $sub['interval_days'] ?> days</p>
                  <p class="text-sm mt-1">
                    <span class="badge badge-info capitalize"><?= e($sub['status']) ?></span>
                    <?php if ($sub['status'] === 'active'): ?>
                      <span class="text-ink/60 ml-2">Next: <?= date('d M Y', strtotime((string) $sub['next_order_date'])) ?></span>
                    <?php endif; ?>
                  </p>
                </div>
                <div class="flex flex-wrap gap-2">
                  <?php if ($sub['status'] === 'active'): ?>
                    <form method="POST" action="/account/subscriptions/<?= (int) $sub['id'] ?>/skip">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary btn-sm">Skip next</button>
                    </form>
                    <form method="POST" action="/account/subscriptions/<?= (int) $sub['id'] ?>/pause">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary btn-sm">Pause</button>
                    </form>
                  <?php elseif ($sub['status'] === 'paused'): ?>
                    <form method="POST" action="/account/subscriptions/<?= (int) $sub['id'] ?>/resume">
                      <?= csrf_field() ?>
                      <button type="submit" class="text-xs font-semibold border-2 border-fern text-fern px-3 py-1.5 hover:bg-fern hover:text-paper">Resume</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($sub['status'] !== 'cancelled'): ?>
                    <form method="POST" action="/account/subscriptions/<?= (int) $sub['id'] ?>/cancel">
                      <?= csrf_field() ?>
                      <button type="submit" class="text-xs font-semibold text-leash hover:underline">Cancel</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
