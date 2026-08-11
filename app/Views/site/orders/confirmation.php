<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $order */
/** @var array $items */
/** @var array $crossSell */
?>

<section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
  <div class="text-center">
    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-fern text-paper">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <h1 class="font-display text-3xl font-bold mt-6">Order confirmed</h1>
    <p class="mt-2 text-ink/60">Order <strong><?= e($order['order_number']) ?></strong> &middot; we'll email updates as it moves.</p>
  </div>

  <div class="mt-10 card-tag p-6">
    <div class="card-tag__tab"><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Paid online' ?></div>
    <ul class="space-y-3 text-sm mt-6">
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

  <div class="mt-6 grid sm:grid-cols-2 gap-4">
    <div class="card-tag p-5">
      <p class="text-sm font-semibold uppercase tracking-wide text-ink/50">Delivering to</p>
      <p class="mt-2 text-sm"><?= e($order['shipping_full_name']) ?><br>
      <?= e($order['shipping_line1']) ?><?= $order['shipping_line2'] ? ', ' . e($order['shipping_line2']) : '' ?><br>
      <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> <?= e($order['shipping_postal_code']) ?></p>
    </div>
    <div class="card-tag p-5 flex flex-col justify-center gap-3">
      <a href="/orders/track/<?= e($order['order_number']) ?><?= $order['user_id'] === null ? '?email=' . urlencode((string) $order['guest_email']) : '' ?>"
         class="text-center btn btn-primary">Track this order</a>
      <a href="/shop" class="text-center text-sm font-semibold text-ink/60 hover:text-leash">Continue shopping</a>
    </div>
  </div>

  <?php if ($crossSell !== []): ?>
    <div class="mt-12 border-t-2 border-ink pt-8">
      <h2 class="font-display text-xl font-bold">Others who bought this also got</h2>
      <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($crossSell as $p): ?>
          <a href="/shop/<?= e($p['slug']) ?>" class="card-tag group">
            <?php \App\Core\View::include('components/product-image', ['product' => $p]); ?>
            <div class="p-3">
              <h3 class="font-semibold text-sm group-hover:text-leash"><?= e($p['name']) ?></h3>
              <p class="mt-1 font-display font-bold text-sm"><?= $p['min_price_paise'] ? money((int) $p['min_price_paise']) : '' ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
