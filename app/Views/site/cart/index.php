<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $items */
/** @var array $totals */
/** @var array $crossSell */
?>

<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-3xl font-bold">Your cart</h1>

  <?php if ($items === []): ?>
    <div class="mt-8 card-tag p-10 text-center">
      <span class="icon-chip icon-chip-leash mx-auto"><?= icon('cart', 'h-5 w-5') ?></span>
      <p class="font-display text-xl font-semibold mt-4">Your cart is empty.</p>
      <p class="mt-2 text-ink/60">Find something your pet will love.</p>
      <a href="/shop" class="btn btn-primary mt-6">Browse the shop</a>
    </div>
  <?php else: ?>
    <div class="mt-8 grid lg:grid-cols-[1fr_320px] gap-10">
      <div class="space-y-4">
        <?php foreach ($items as $item): ?>
          <div class="card-tag flex gap-4 p-4">
            <div class="w-24 shrink-0">
              <?php \App\Core\View::include('components/product-image', ['product' => ['pet_type' => $item['pet_type']], 'class' => 'aspect-square']); ?>
            </div>
            <div class="flex-1">
              <a href="/shop/<?= e($item['product_slug']) ?>" class="font-semibold hover:text-leash"><?= e($item['product_name']) ?></a>
              <p class="text-sm text-ink/60"><?= e($item['variant_label']) ?></p>
              <?php if ((int) $item['quantity'] > (int) $item['stock_quantity']): ?>
                <p class="mt-1 text-xs font-semibold text-leash">Only <?= (int) $item['stock_quantity'] ?> left — reduce quantity to check out.</p>
              <?php endif; ?>
              <div class="mt-3 flex items-center gap-3">
                <form method="POST" action="/cart/update" class="flex items-center gap-2">
                  <?= csrf_field() ?>
                  <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                  <label class="sr-only" for="qty-<?= (int) $item['id'] ?>">Quantity</label>
                  <input id="qty-<?= (int) $item['id'] ?>" type="number" name="qty" value="<?= (int) $item['quantity'] ?>" min="1"
                         class="w-16 border-2 border-ink px-2 py-1 text-center text-sm">
                  <button type="submit" class="text-sm font-semibold text-ink/60 hover:text-leash">Update</button>
                </form>
                <form method="POST" action="/cart/remove">
                  <?= csrf_field() ?>
                  <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                  <button type="submit" class="flex items-center gap-1 text-sm font-semibold text-danger hover:underline"><?= icon('trash', 'h-3.5 w-3.5') ?> Remove</button>
                </form>
              </div>
            </div>
            <div class="text-right font-display font-bold">
              <?= money((int) $item['price_paise'] * (int) $item['quantity']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="card-tag p-6 h-fit sticky top-24">
        <div class="card-tag__tab">Summary</div>
        <?php if ($totals['coupon']): ?>
          <form method="POST" action="/cart/coupon/remove" class="flex items-center justify-between mb-4 mt-6 text-sm bg-fern/10 border-2 border-fern px-3 py-2">
            <?= csrf_field() ?>
            <span class="flex items-center gap-1.5"><?= icon('tag', 'h-4 w-4 text-fern') ?> Coupon <strong><?= e($totals['coupon']['code']) ?></strong> applied</span>
            <button type="submit" class="text-leash font-semibold hover:underline">Remove</button>
          </form>
        <?php else: ?>
          <form method="POST" action="/cart/coupon" class="flex gap-2 mb-4 mt-6">
            <?= csrf_field() ?>
            <label class="sr-only" for="coupon-code">Coupon code</label>
            <input id="coupon-code" type="text" name="code" placeholder="Coupon code" class="input flex-1 text-sm uppercase">
            <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
          </form>
        <?php endif; ?>

        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-ink/60">Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
          <?php if ($totals['discount'] > 0): ?>
            <div class="flex justify-between text-fern"><dt>Discount</dt><dd>&minus;<?= money($totals['discount']) ?></dd></div>
          <?php endif; ?>
          <div class="flex justify-between">
            <dt class="text-ink/60">Shipping</dt>
            <dd><?= $totals['shipping'] === 0 ? 'Free' : money($totals['shipping']) ?></dd>
          </div>
          <div class="flex justify-between"><dt class="text-ink/60">Tax</dt><dd><?= money($totals['tax']) ?></dd></div>
        </dl>
        <div class="mt-4 pt-4 border-t-2 border-ink flex justify-between font-display text-lg font-bold">
          <span>Total</span><span><?= money($totals['total']) ?></span>
        </div>
        <?php if ($totals['shipping'] > 0): ?>
          <p class="mt-2 text-xs text-ink/50">Add <?= money($totals['freeShippingRemaining']) ?> more for free shipping.</p>
        <?php endif; ?>

        <a href="/checkout" class="btn btn-primary mt-6 w-full"><?= icon('arrow-right', 'h-4 w-4') ?> Checkout</a>
      </div>
    </div>

    <?php if ($crossSell !== []): ?>
      <div class="mt-12 border-t-2 border-ink pt-8">
        <h2 class="font-display text-xl font-bold">Often bought together with your cart</h2>
        <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <?php foreach ($crossSell as $p): ?>
            <a href="/shop/<?= e($p['slug']) ?>" class="card-tag card-tag--pop group">
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
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
