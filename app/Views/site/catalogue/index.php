<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $result */
/** @var array $filters */
/** @var array $categories */
/** @var array $brands */

$items = $result['items'];
$total = $result['total'];
$perPage = $result['perPage'];
$page = $result['page'];
$totalPages = max(1, (int) ceil($total / $perPage));
?>

<section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" data-reveal>
    <div>
      <h1 class="font-display text-3xl font-bold"><?= !empty($filters['q']) ? 'Search results for "' . e($filters['q']) . '"' : 'Shop' ?></h1>
      <p class="mt-1 text-ink/60"><?= $total ?> product<?= $total === 1 ? '' : 's' ?></p>
    </div>

    <form method="GET" action="/shop" class="flex items-center gap-3">
      <?php foreach (['category', 'pet', 'stage', 'q'] as $carry): ?>
        <?php if (!empty($filters[$carry])): ?>
          <input type="hidden" name="<?= e($carry) ?>" value="<?= e($filters[$carry]) ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <label for="sort" class="sr-only">Sort by</label>
      <select id="sort" name="sort" onchange="this.form.submit()" class="input !w-auto text-sm font-medium">
        <?php foreach ([
          'newest' => 'Newest',
          'price_asc' => 'Price: low to high',
          'price_desc' => 'Price: high to low',
          'rating' => 'Top rated',
        ] as $val => $label): ?>
          <option value="<?= e($val) ?>" <?= ($filters['sort'] ?? 'newest') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <div class="mt-8 grid lg:grid-cols-[260px_1fr] gap-10">
    <!-- Facets -->
    <aside class="card-tag p-6 h-fit space-y-8" data-reveal>
      <form method="GET" action="/shop" class="space-y-8">
        <?php if (!empty($filters['q'])): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>

        <div>
          <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink/50"><?= icon('paw', 'h-3.5 w-3.5') ?> Pet</h2>
          <ul class="mt-3 space-y-2 text-sm">
            <?php foreach ($result['facets']['pet_types'] as $pt): ?>
              <li>
                <a href="<?= e(query_with($filters, ['pet' => $filters['pet'] === $pt['pet_type'] ? null : $pt['pet_type'], 'page' => null])) ?>"
                   class="flex justify-between hover:text-leash <?= ($filters['pet'] ?? '') === $pt['pet_type'] ? 'font-semibold text-leash' : '' ?>">
                  <span class="capitalize"><?= e(str_replace('_', ' ', $pt['pet_type'])) ?></span>
                  <span class="text-ink/40"><?= (int) $pt['product_count'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div>
          <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink/50"><?= icon('box', 'h-3.5 w-3.5') ?> Category</h2>
          <ul class="mt-3 space-y-2 text-sm">
            <?php foreach ($result['facets']['categories'] as $cat): ?>
              <li>
                <a href="<?= e(query_with($filters, ['category' => $filters['category'] === $cat['slug'] ? null : $cat['slug'], 'page' => null])) ?>"
                   class="flex justify-between hover:text-leash <?= ($filters['category'] ?? '') === $cat['slug'] ? 'font-semibold text-leash' : '' ?>">
                  <span><?= e($cat['name']) ?></span>
                  <span class="text-ink/40"><?= (int) $cat['product_count'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div>
          <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink/50"><?= icon('tag', 'h-3.5 w-3.5') ?> Brand</h2>
          <ul class="mt-3 space-y-2 text-sm max-h-48 overflow-y-auto">
            <?php foreach ($result['facets']['brands'] as $brand): ?>
              <li class="flex items-center gap-2">
                <input type="checkbox" id="brand-<?= (int) $brand['id'] ?>" name="brand[]" value="<?= e($brand['slug']) ?>"
                       <?= in_array($brand['slug'], (array) ($filters['brand'] ?? []), true) ? 'checked' : '' ?>
                       class="rounded accent-[#7c3aed] w-4 h-4">
                <label for="brand-<?= (int) $brand['id'] ?>" class="flex-1 flex justify-between">
                  <span><?= e($brand['name']) ?></span>
                  <span class="text-ink/40"><?= (int) $brand['product_count'] ?></span>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div>
          <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink/50"><?= icon('credit-card', 'h-3.5 w-3.5') ?> Price (₹)</h2>
          <div class="mt-3 flex items-center gap-2">
            <input type="number" name="price_min" placeholder="Min" value="<?= e($filters['price_min'] ?? '') ?>" class="input !w-full px-2 py-1.5 text-sm">
            <span>–</span>
            <input type="number" name="price_max" placeholder="Max" value="<?= e($filters['price_max'] ?? '') ?>" class="input !w-full px-2 py-1.5 text-sm">
          </div>
        </div>

        <div>
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?> class="border-2 border-ink">
            In stock only
          </label>
        </div>

        <button type="submit" class="btn btn-primary w-full">Apply filters</button>
        <a href="/shop" class="block text-center text-sm text-ink/50 hover:text-leash">Clear all</a>
      </form>
    </aside>

    <!-- Results -->
    <div>
      <?php if ($items === []): ?>
        <div class="card-tag p-10 text-center" data-reveal>
          <span class="icon-chip icon-chip-leash mx-auto"><?= icon('search', 'h-5 w-5') ?></span>
          <p class="font-display text-xl font-semibold mt-4">No products match those filters.</p>
          <p class="mt-2 text-ink/60">Try clearing a filter or searching a different term.</p>
          <a href="/shop" class="btn btn-primary mt-6">Clear filters</a>
        </div>
      <?php else: ?>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <?php foreach ($items as $i => $product): ?>
            <a href="/shop/<?= e($product['slug']) ?>" class="card-tag card-tag--pop group flex flex-col" data-reveal style="transition-delay:<?= ($i % 6) * 60 ?>ms">
              <?php if ((int) $product['total_stock'] <= 0): ?>
                <div class="card-tag__tab !bg-ink !text-paper">Out of stock</div>
              <?php elseif ($product['compare_at_price_paise']): ?>
                <div class="card-tag__tab">Sale</div>
              <?php endif; ?>
              <?php \App\Core\View::include('components/product-image', ['product' => $product]); ?>
              <div class="p-4 flex-1 flex flex-col">
                <?php if ($product['brand_name']): ?>
                  <p class="text-xs uppercase tracking-wide text-ink/50"><?= e($product['brand_name']) ?></p>
                <?php endif; ?>
                <h3 class="font-semibold mt-1 group-hover:text-leash transition-colors duration-150"><?= e($product['name']) ?></h3>
                <div class="mt-1 flex items-center gap-1 text-xs text-ink/50">
                  <span class="flex items-center gap-0.5 text-tennis" aria-hidden="true"><?= icon('star', 'h-3 w-3') ?> <?= number_format((float) $product['avg_rating'], 1) ?></span>
                  <span>(<?= (int) $product['review_count'] ?>)</span>
                </div>
                <div class="mt-auto pt-3 flex items-baseline gap-2">
                  <span class="font-display text-lg font-bold"><?= money((int) $product['min_price_paise']) ?></span>
                  <?php if ($product['compare_at_price_paise']): ?>
                    <span class="text-sm text-ink/40 line-through"><?= money((int) $product['compare_at_price_paise']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <nav class="mt-10 flex justify-center gap-2" aria-label="Pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <a href="<?= e(query_with($filters, ['page' => $p])) ?>"
                 class="w-10 h-10 flex items-center justify-center rounded-full font-medium transition-all duration-200 <?= $p === $page ? 'text-white shadow-lg' : 'glass hover:-translate-y-0.5' ?>"
                 <?= $p === $page ? 'style="background: linear-gradient(135deg, var(--lav), var(--blush));" aria-current="page"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
