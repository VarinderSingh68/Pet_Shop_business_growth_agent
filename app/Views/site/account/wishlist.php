<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $items */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'wishlist']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">My wishlist</h1>

      <?php if ($items === []): ?>
        <div class="mt-8 card-tag p-10 text-center">
          <p class="font-semibold">Your wishlist is empty.</p>
          <a href="/shop" class="mt-4 inline-block btn btn-primary">Browse the shop</a>
        </div>
      <?php else: ?>
        <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <?php foreach ($items as $item): ?>
            <div class="card-tag">
              <a href="/shop/<?= e($item['slug']) ?>">
                <?php \App\Core\View::include('components/product-image', ['product' => $item]); ?>
              </a>
              <div class="p-4">
                <a href="/shop/<?= e($item['slug']) ?>" class="font-semibold hover:text-leash"><?= e($item['name']) ?></a>
                <p class="mt-1 font-display font-bold"><?= $item['min_price_paise'] ? money((int) $item['min_price_paise']) : '' ?></p>
                <?php if ((int) $item['total_stock'] <= 0): ?>
                  <p class="text-xs text-leash font-semibold mt-1">Out of stock</p>
                <?php endif; ?>
                <form method="POST" action="/wishlist/toggle" class="mt-3">
                  <?= csrf_field() ?>
                  <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                  <button type="submit" class="text-xs font-semibold text-ink/50 hover:text-leash">Remove</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
