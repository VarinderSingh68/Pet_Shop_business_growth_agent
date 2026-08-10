<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $user */
/** @var array $recentOrders */
/** @var array $pets */
/** @var int $wishlistCount */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'index']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">Welcome back, <?= e(explode(' ', (string) $user['name'])[0]) ?></h1>

      <div class="mt-8 grid sm:grid-cols-3 gap-4">
        <a href="/account/orders" class="card-tag card-tag--pop stat-tile p-5 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-leash group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('cart', 'h-5 w-5') ?></span>
          <p class="text-xs uppercase tracking-wide opacity-60 mt-3">Orders</p>
          <p class="stat-tile__value mt-1" data-count-to="<?= count($recentOrders) ?>" <?= count($recentOrders) > 0 ? 'data-count-suffix="+"' : '' ?>>0</p>
        </a>
        <a href="/account/pets" class="card-tag card-tag--pop stat-tile p-5 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-tennis group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('paw', 'h-5 w-5') ?></span>
          <p class="text-xs uppercase tracking-wide opacity-60 mt-3">Pets</p>
          <p class="stat-tile__value mt-1" data-count-to="<?= count($pets) ?>">0</p>
        </a>
        <a href="/account/wishlist" class="card-tag card-tag--pop stat-tile p-5 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-plum group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('heart', 'h-5 w-5') ?></span>
          <p class="text-xs uppercase tracking-wide opacity-60 mt-3">Wishlist</p>
          <p class="stat-tile__value mt-1" data-count-to="<?= (int) $wishlistCount ?>">0</p>
        </a>
      </div>

      <div class="mt-10">
        <div class="flex items-center justify-between">
          <h2 class="font-display text-lg font-semibold">Recent orders</h2>
          <a href="/account/orders" class="text-sm font-semibold text-leash hover:underline">View all</a>
        </div>
        <?php if ($recentOrders === []): ?>
          <p class="mt-3 text-ink/60 text-sm">No orders yet. <a href="/shop" class="text-leash hover:underline">Start shopping</a>.</p>
        <?php else: ?>
          <div class="mt-3 space-y-2">
            <?php
              $statusBadge = ['pending_payment' => 'badge-warning', 'confirmed' => 'badge-info', 'processing' => 'badge-info', 'shipped' => 'badge-info', 'delivered' => 'badge-success', 'cancelled' => 'badge-danger', 'refunded' => 'badge-neutral'];
            ?>
            <?php foreach ($recentOrders as $order): ?>
              <a href="/orders/track/<?= e($order['order_number']) ?>" class="flex items-center justify-between border-2 border-ink px-4 py-3 text-sm hover:border-leash transition-colors duration-150">
                <span class="flex items-center gap-2.5"><span class="text-ink/40"><?= icon('cart', 'h-4 w-4') ?></span> <?= e($order['order_number']) ?> <span class="text-ink/50">&middot; <?= date('d M Y', strtotime((string) $order['created_at'])) ?></span></span>
                <span class="badge <?= $statusBadge[$order['status']] ?? 'badge-neutral' ?>"><?= e(str_replace('_', ' ', $order['status'])) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="mt-10 grid sm:grid-cols-2 gap-4">
        <a href="/account/appointments" class="card-tag card-tag--pop p-4 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-info group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('calendar', 'h-4 w-4') ?></span>
          <p class="font-semibold text-sm mt-3">Appointments</p>
          <p class="text-xs mt-1 opacity-70">Grooming, boarding, vet visits</p>
        </a>
        <a href="/account/subscriptions" class="card-tag card-tag--pop p-4 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-fern group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('refresh', 'h-4 w-4') ?></span>
          <p class="font-semibold text-sm mt-3">Subscriptions</p>
          <p class="text-xs mt-1 opacity-70">Repeat food deliveries</p>
        </a>
        <a href="/account/rewards" class="card-tag card-tag--pop p-4 hover:bg-ink hover:text-paper transition-colors group">
          <span class="icon-chip icon-chip-tennis group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('gift', 'h-4 w-4') ?></span>
          <p class="font-semibold text-sm mt-3">Loyalty points</p>
          <p class="text-xs mt-1 opacity-70">Earn and redeem rewards</p>
        </a>
        <div class="card-tag p-4 opacity-60">
          <span class="icon-chip !bg-mist !text-ink/40"><?= icon('users', 'h-4 w-4') ?></span>
          <p class="font-semibold text-sm mt-3">Referral link</p>
          <p class="text-xs mt-1">Ships in a later build phase.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
