<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $order */
/** @var array $items */
/** @var array $history */
/** @var string $invoiceUrl */

$steps = ['pending_payment' => 'Order placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
$currentIndex = array_search($order['status'], array_keys($steps), true);
?>

<section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
  <div class="flex items-center justify-between flex-wrap gap-4">
    <div>
      <h1 class="font-display text-2xl font-bold">Order <?= e($order['order_number']) ?></h1>
      <p class="text-ink/60 text-sm mt-1">Placed <?= date('d M Y', strtotime((string) $order['placed_at'])) ?></p>
    </div>
    <a href="<?= e($invoiceUrl) ?>" class="btn btn-secondary btn-sm">Download invoice</a>
  </div>

  <?php if (in_array($order['status'], ['cancelled', 'refunded'], true)): ?>
    <div class="mt-8 border-2 border-leash bg-leash/10 px-4 py-3 text-sm font-semibold capitalize"><?= e(str_replace('_', ' ', $order['status'])) ?></div>
  <?php else: ?>
    <ol class="mt-10 flex justify-between text-xs sm:text-sm">
      <?php foreach (array_values($steps) as $i => $label): ?>
        <li class="flex-1 text-center relative">
          <div class="mx-auto h-3 w-3 rounded-full <?= $i <= $currentIndex ? 'bg-leash' : 'bg-mist' ?>"></div>
          <?php if ($i < count($steps) - 1): ?>
            <div class="absolute top-1.5 left-1/2 w-full h-0.5 <?= $i < $currentIndex ? 'bg-leash' : 'bg-mist' ?>"></div>
          <?php endif; ?>
          <p class="mt-2 <?= $i <= $currentIndex ? 'font-semibold' : 'text-ink/40' ?>"><?= e($label) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>

  <div class="mt-12 card-tag p-6">
    <div class="card-tag__tab">Items</div>
    <ul class="space-y-3 text-sm">
      <?php foreach ($items as $item): ?>
        <li class="flex justify-between">
          <span><?= e($item['product_name_snapshot']) ?> <span class="text-ink/50">(<?= e($item['variant_label_snapshot']) ?>) &times;<?= (int) $item['quantity'] ?></span></span>
          <span><?= money((int) $item['line_total_paise']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="mt-4 pt-4 border-t-2 border-mist flex justify-between font-display text-lg font-bold">
      <span>Total</span><span><?= money((int) $order['total_paise']) ?></span>
    </div>
  </div>

  <div class="mt-6">
    <h2 class="font-display text-lg font-semibold">Status history</h2>
    <ul class="mt-3 space-y-2 text-sm">
      <?php foreach (array_reverse($history) as $h): ?>
        <li class="flex justify-between border-b border-mist pb-2">
          <span class="capitalize"><?= e(str_replace('_', ' ', $h['status'])) ?><?= $h['note'] ? ' — ' . e($h['note']) : '' ?></span>
          <span class="text-ink/40"><?= e($h['created_at']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
