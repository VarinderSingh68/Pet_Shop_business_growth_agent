<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $product */
/** @var array $variants */
/** @var array $images */
/** @var array $reviews */
/** @var array $related */

$variantsJson = json_encode(array_map(static function (array $v) use ($product) {
    $days = null;
    if (!empty($v['weight_grams']) && !empty($product['feeding_grams_per_day'])) {
        $days = (int) round((int) $v['weight_grams'] / (int) $product['feeding_grams_per_day']);
    }
    return [
        'id' => (int) $v['id'],
        'label' => $v['label'],
        'price' => money((int) $v['price_paise']),
        'compareAt' => $v['compare_at_price_paise'] ? money((int) $v['compare_at_price_paise']) : null,
        'stock' => (int) $v['stock_quantity'],
        'lowStock' => \App\Models\ProductVariant::isLowStock($v),
        'days' => $days,
    ];
}, $variants), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$defaultVariant = $variants[0] ?? null;
?>

<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10"
         x-data='{
            variants: <?= $variantsJson ?>,
            selected: <?= $defaultVariant ? (int) $defaultVariant['id'] : 'null' ?>,
            get variant() { return this.variants.find(v => v.id === this.selected) },
            zoom: false,
         }'>

  <nav class="text-sm text-ink/50" aria-label="Breadcrumb">
    <a href="/shop" class="hover:text-leash">Shop</a> <span aria-hidden="true">/</span>
    <span class="text-ink"><?= e($product['name']) ?></span>
  </nav>

  <div class="mt-6 grid lg:grid-cols-2 gap-12">
    <!-- Gallery -->
    <div>
      <?php if ($images === []): ?>
        <div class="relative cursor-zoom-in" @click="zoom = !zoom" :class="zoom ? 'cursor-zoom-out' : ''">
          <?php \App\Core\View::include('components/product-image', ['product' => $product, 'class' => 'aspect-square transition-transform duration-150 ' . '']); ?>
        </div>
      <?php else: ?>
        <div class="card-tag aspect-square overflow-hidden cursor-zoom-in" @click="zoom = !zoom">
          <img src="<?= e(media_url($images[0]['path'])) ?>" alt="<?= e($images[0]['alt_text'] ?? $product['name']) ?>"
               class="w-full h-full object-cover transition-transform duration-150" :class="zoom ? 'scale-150' : 'scale-100'">
        </div>
        <?php if (count($images) > 1): ?>
          <div class="mt-3 grid grid-cols-5 gap-2">
            <?php foreach (array_slice($images, 1, 5) as $img): ?>
              <div class="card-tag aspect-square overflow-hidden">
                <img src="<?= e(media_url($img['path'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>" loading="lazy" class="w-full h-full object-cover">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Details -->
    <div data-reveal>
      <?php if (!empty($product['brand_name'] ?? null)): ?>
        <p class="text-sm uppercase tracking-wide text-ink/50"><?= e($product['brand_name']) ?></p>
      <?php endif; ?>
      <h1 class="font-display text-3xl font-bold mt-1"><?= e($product['name']) ?></h1>

      <div class="mt-2 flex items-center gap-2 text-sm">
        <span aria-hidden="true" class="flex items-center gap-0.5 text-tennis"><?= icon('star', 'h-4 w-4') ?></span>
        <span class="text-ink/60"><?= number_format((float) $product['avg_rating'], 1) ?> (<?= (int) $product['review_count'] ?> review<?= (int) $product['review_count'] === 1 ? '' : 's' ?>)</span>
      </div>

      <p class="mt-4 text-ink/75"><?= e($product['short_description']) ?></p>

      <div class="mt-6 flex items-baseline gap-3">
        <span class="font-display text-3xl font-bold" x-text="variant ? variant.price : ''"></span>
        <span class="text-ink/40 line-through" x-show="variant && variant.compareAt" x-text="variant ? variant.compareAt : ''"></span>
      </div>

      <p class="mt-2 text-sm text-fern font-medium" x-show="variant && variant.days">
        Feeds your pet for about <span x-text="variant ? variant.days : ''"></span> days at the typical daily amount.
      </p>

      <template x-if="variant && variant.stock <= 0">
        <p class="mt-2 text-sm font-semibold text-leash">Out of stock</p>
      </template>
      <template x-if="variant && variant.stock > 0 && variant.lowStock">
        <p class="mt-2 text-sm font-semibold text-tennis">Only <span x-text="variant.stock"></span> left</p>
      </template>
      <template x-if="variant && variant.stock > 0 && !variant.lowStock">
        <p class="mt-2 text-sm text-fern font-medium">In stock</p>
      </template>

      <!-- Variant picker -->
      <fieldset class="mt-6">
        <legend class="text-sm font-semibold mb-2">Choose an option</legend>
        <div class="flex flex-wrap gap-2">
          <template x-for="v in variants" :key="v.id">
            <button type="button" @click="selected = v.id"
                    :class="selected === v.id ? 'text-white shadow-lg' : 'glass hover:-translate-y-0.5'"
                    :style="selected === v.id ? 'background: linear-gradient(135deg, var(--lav), var(--blush))' : ''"
                    :disabled="v.stock <= 0"
                    class="rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                    x-text="v.label"></button>
          </template>
        </div>
      </fieldset>

      <form method="POST" action="/cart/add" class="mt-8 flex gap-3">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
        <input type="hidden" name="variant_id" x-bind:value="selected">
        <input type="number" name="qty" value="1" min="1" class="input w-20 !rounded-full text-center">
        <button type="submit" :disabled="!variant || variant.stock <= 0"
                class="btn btn-primary flex-1 disabled:opacity-40 disabled:cursor-not-allowed">
          <?= icon('cart', 'h-4 w-4') ?> Add to cart
        </button>
      </form>

      <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-ink/60">
        <span class="flex items-center gap-1.5"><?= icon('truck', 'h-4 w-4 text-fern') ?> Same-day dispatch</span>
        <span class="flex items-center gap-1.5"><?= icon('refresh', 'h-4 w-4 text-fern') ?> Easy returns</span>
        <span class="flex items-center gap-1.5"><?= icon('shield-check', 'h-4 w-4 text-fern') ?> Secure payment</span>
      </div>

      <?php if (auth()->check()): ?>
        <form method="POST" action="/wishlist/toggle" class="mt-4">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <button type="submit" class="flex items-center gap-1.5 text-sm font-semibold <?= $isWishlisted ? 'text-leash' : 'text-ink/60 hover:text-leash' ?>">
            <?= icon('heart', 'h-4 w-4' . ($isWishlisted ? '' : '')) ?> <?= $isWishlisted ? 'Saved to wishlist' : 'Save to wishlist' ?>
          </button>
        </form>
      <?php else: ?>
        <a href="/account/login" class="mt-4 flex items-center gap-1.5 text-sm font-semibold text-ink/60 hover:text-leash"><?= icon('heart', 'h-4 w-4') ?> Sign in to save to wishlist</a>
      <?php endif; ?>

      <?php if ($product['feeding_grams_per_day']): ?>
        <div class="mt-6 card-tag p-5" style="background: linear-gradient(135deg, rgba(134,239,172,0.18), rgba(125,211,252,0.18));">
          <p class="flex items-center gap-2 text-sm font-semibold text-[#0b3d2e]"><span class="icon-chip icon-chip-fern !w-7 !h-7"><?= icon('refresh', 'h-3.5 w-3.5') ?></span> Subscribe &amp; never run out</p>
          <p class="text-xs text-ink/60 mt-2">Deliver automatically on a schedule. Pause, skip, or cancel anytime.</p>
          <?php if (auth()->check()): ?>
            <form method="POST" action="/account/subscriptions" class="mt-3 flex flex-wrap items-end gap-3">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
              <input type="hidden" name="variant_id" x-bind:value="selected">
              <div>
                <label for="sub_interval" class="block text-xs font-semibold mb-1">Every</label>
                <select id="sub_interval" name="interval_days" class="input !rounded-full !py-1.5 text-sm">
                  <option value="14">14 days</option>
                  <option value="30" selected>30 days</option>
                  <option value="45">45 days</option>
                  <option value="60">60 days</option>
                </select>
              </div>
              <button type="submit" class="btn btn-sm">Subscribe</button>
            </form>
          <?php else: ?>
            <a href="/account/login" class="mt-3 inline-block text-sm font-semibold text-[#0b3d2e] hover:underline">Sign in to subscribe</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($product['description']): ?>
        <div class="mt-10 border-t border-white/50 pt-6">
          <h2 class="font-display text-lg font-semibold">About this product</h2>
          <p class="mt-3 text-ink/75 whitespace-pre-line"><?= e($product['description']) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Reviews -->
  <div class="mt-16 border-t border-white/50 pt-10" data-reveal>
    <h2 class="font-display text-2xl font-bold">Customer reviews</h2>
    <?php if ($reviews === []): ?>
      <p class="mt-3 text-ink/60">No reviews yet — be the first to share how it went.</p>
    <?php else: ?>
      <div class="mt-6 grid md:grid-cols-2 gap-6">
        <?php foreach ($reviews as $review): ?>
          <div class="card-tag card-tag--pop p-5">
            <div class="flex items-center justify-between">
              <p class="font-semibold"><?= e($review['reviewer_name'] ?? 'Verified customer') ?></p>
              <?php if ($review['is_verified_purchase']): ?>
                <span class="badge badge-success"><?= icon('check', 'h-3 w-3') ?> Verified</span>
              <?php endif; ?>
            </div>
            <p class="flex items-center gap-0.5 text-tennis text-sm mt-2" aria-hidden="true">
              <?php for ($r = 1; $r <= 5; $r++): ?>
                <?= icon('star', 'h-3.5 w-3.5' . ($r > (int) $review['rating'] ? ' opacity-25' : '')) ?>
              <?php endfor; ?>
            </p>
            <?php if ($review['title']): ?><p class="font-semibold mt-2"><?= e($review['title']) ?></p><?php endif; ?>
            <p class="mt-1 text-sm text-ink/75"><?= e($review['body']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (auth()->check()): ?>
      <details class="mt-8">
        <summary class="cursor-pointer text-sm font-semibold text-leash">Write a review</summary>
        <form method="POST" action="/reviews" class="mt-4 max-w-lg space-y-4 card-tag p-5">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <div>
            <label for="review_rating" class="block text-sm font-semibold mb-1">Rating</label>
            <select id="review_rating" name="rating" required class="input">
              <option value="">Choose a rating</option>
              <?php for ($r = 5; $r >= 1; $r--): ?>
                <option value="<?= $r ?>"><?= str_repeat('★', $r) ?> (<?= $r ?>/5)</option>
              <?php endfor; ?>
            </select>
          </div>
          <div>
            <label for="review_title" class="block text-sm font-semibold mb-1">Title <span class="font-normal text-ink/50">(optional)</span></label>
            <input id="review_title" type="text" name="title" class="input">
          </div>
          <div>
            <label for="review_body" class="block text-sm font-semibold mb-1">Your review <span class="font-normal text-ink/50">(optional)</span></label>
            <textarea id="review_body" name="body" rows="3" class="input"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit review</button>
        </form>
      </details>
    <?php else: ?>
      <p class="mt-6 text-sm text-ink/60"><a href="/account/login" class="text-[#7c3aed] hover:underline">Sign in</a> to write a review.</p>
    <?php endif; ?>
  </div>

  <!-- Related -->
  <?php if ($related !== []): ?>
    <div class="mt-16 border-t border-white/50 pt-10" data-reveal>
      <h2 class="font-display text-2xl font-bold"><?= $relatedIsCoPurchase ? 'Frequently bought together' : 'You might also like' ?></h2>
      <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($related as $rp): ?>
          <a href="/shop/<?= e($rp['slug']) ?>" class="card-tag card-tag--pop group">
            <?php \App\Core\View::include('components/product-image', ['product' => $rp]); ?>
            <div class="p-4">
              <h3 class="font-semibold text-sm group-hover:text-[#7c3aed]"><?= e($rp['name']) ?></h3>
              <p class="mt-1 font-display font-bold"><?= $rp['min_price_paise'] ? money((int) $rp['min_price_paise']) : '' ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
