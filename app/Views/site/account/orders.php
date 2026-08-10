<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $orders */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'orders']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">My orders</h1>

      <?php if ($orders === []): ?>
        <div class="mt-8 card-tag p-10 text-center">
          <p class="font-semibold">No orders yet.</p>
          <a href="/shop" class="mt-4 inline-block btn btn-primary">Browse the shop</a>
        </div>
      <?php else: ?>
        <div class="mt-6 space-y-3">
          <?php foreach ($orders as $order): ?>
            <a href="/orders/track/<?= e($order['order_number']) ?>" class="card-tag flex items-center justify-between p-4 hover:border-leash">
              <div>
                <p class="font-semibold"><?= e($order['order_number']) ?></p>
                <p class="text-sm text-ink/50"><?= date('d M Y', strtotime((string) $order['created_at'])) ?> &middot; <?= money((int) $order['total_paise']) ?></p>
              </div>
              <span class="badge badge-info capitalize"><?= e(str_replace('_', ' ', $order['status'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
