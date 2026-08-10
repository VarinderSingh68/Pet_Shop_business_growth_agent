<?php
/** @var string $active */
$links = [
    'index' => ['/account', 'Overview'],
    'orders' => ['/account/orders', 'Orders'],
    'appointments' => ['/account/appointments', 'Appointments'],
    'subscriptions' => ['/account/subscriptions', 'Subscriptions'],
    'pets' => ['/account/pets', 'My pets'],
    'addresses' => ['/account/addresses', 'Addresses'],
    'wishlist' => ['/account/wishlist', 'Wishlist'],
    'rewards' => ['/account/rewards', 'Rewards'],
    'support' => ['/account/support', 'Support'],
];
?>
<nav class="flex lg:flex-col gap-1 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0" aria-label="Account">
  <?php foreach ($links as $key => [$href, $label]): ?>
    <a href="<?= e($href) ?>"
       class="px-3 py-2 text-sm font-medium whitespace-nowrap <?= $active === $key ? 'bg-ink text-paper' : 'hover:bg-mist' ?>">
      <?= e($label) ?>
    </a>
  <?php endforeach; ?>
</nav>
