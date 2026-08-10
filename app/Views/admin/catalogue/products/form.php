<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array|null $product */
/** @var array $variants */
/** @var array $images */
/** @var array $categories */
/** @var array $brands */

$isEdit = $product !== null;
?>

<a href="/admin/catalogue" class="text-sm text-ink/50 hover:text-leash">&larr; Products</a>

<div class="mt-3 max-w-3xl space-y-6">
    <form method="POST" action="<?= $isEdit ? '/admin/catalogue/products/' . (int) $product['id'] : '/admin/catalogue/products' ?>" class="card-tag p-5 bg-white space-y-4">
      <?= csrf_field() ?>
      <div>
        <label for="name" class="block text-sm font-semibold mb-1">Product name</label>
        <input id="name" type="text" name="name" required value="<?= e($product['name'] ?? old('name')) ?>" class="input">
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="category_id" class="block text-sm font-semibold mb-1">Category</label>
          <select id="category_id" name="category_id" required class="input">
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= ($product['category_id'] ?? null) === $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="brand_id" class="block text-sm font-semibold mb-1">Brand</label>
          <select id="brand_id" name="brand_id" class="input">
            <option value="">No brand</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?= (int) $b['id'] ?>" <?= ($product['brand_id'] ?? null) === $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="pet_type" class="block text-sm font-semibold mb-1">Pet type</label>
          <select id="pet_type" name="pet_type" required class="input">
            <?php foreach (['dog', 'cat', 'bird', 'fish', 'small_pet', 'other'] as $pt): ?>
              <option value="<?= $pt ?>" <?= ($product['pet_type'] ?? '') === $pt ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $pt)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="life_stage" class="block text-sm font-semibold mb-1">Life stage</label>
          <select id="life_stage" name="life_stage" required class="input">
            <?php foreach (['all' => 'All stages', 'puppy_kitten' => 'Puppy/Kitten', 'adult' => 'Adult', 'senior' => 'Senior'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($product['life_stage'] ?? 'all') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="status" class="block text-sm font-semibold mb-1">Status</label>
          <select id="status" name="status" class="input">
            <?php foreach (['draft', 'active', 'archived'] as $s): ?>
              <option value="<?= $s ?>" <?= ($product['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="feeding_grams_per_day" class="block text-sm font-semibold mb-1">Feeding grams/day <span class="font-normal text-ink/50">(food only)</span></label>
          <input id="feeding_grams_per_day" type="number" name="feeding_grams_per_day" value="<?= e($product['feeding_grams_per_day'] ?? '') ?>" class="input">
        </div>
      </div>

      <div>
        <label for="short_description" class="block text-sm font-semibold mb-1">Short description</label>
        <input id="short_description" type="text" name="short_description" maxlength="300" value="<?= e($product['short_description'] ?? '') ?>" class="input">
      </div>
      <div>
        <label for="description" class="block text-sm font-semibold mb-1">Full description</label>
        <textarea id="description" name="description" rows="5" class="input"><?= e($product['description'] ?? '') ?></textarea>
      </div>

      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?> class="border-2 border-ink">
        Feature on homepage
      </label>

      <details>
        <summary class="cursor-pointer text-sm font-semibold text-ink/60">SEO (optional)</summary>
        <div class="mt-3 space-y-3">
          <div>
            <label for="meta_title" class="block text-xs font-semibold mb-1">Meta title</label>
            <input id="meta_title" type="text" name="meta_title" value="<?= e($product['meta_title'] ?? '') ?>" class="input text-sm">
          </div>
          <div>
            <label for="meta_description" class="block text-xs font-semibold mb-1">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="2" class="input text-sm"><?= e($product['meta_description'] ?? '') ?></textarea>
          </div>
        </div>
      </details>

      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
    </form>

    <?php if ($isEdit): ?>
      <div class="card-tag p-5 bg-white">
        <p class="font-display font-semibold">Variants</p>
        <div class="mt-3 overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b-2 border-ink text-left">
                <th class="p-2">Label</th><th class="p-2">SKU</th><th class="p-2 text-right">Price</th>
                <th class="p-2 text-right">Compare</th><th class="p-2 text-right">Stock</th><th class="p-2 text-right">Low at</th>
                <th class="p-2">Default</th><th class="p-2"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($variants as $v): ?>
                <tr class="border-b border-mist">
                  <form method="POST" action="/admin/catalogue/products/<?= (int) $product['id'] ?>/variants/<?= (int) $v['id'] ?>">
                    <?= csrf_field() ?>
                    <td class="p-2"><input type="text" name="label" value="<?= e($v['label']) ?>" class="w-28 border border-ink px-1.5 py-1"></td>
                    <td class="p-2 text-ink/60"><?= e($v['sku']) ?></td>
                    <td class="p-2 text-right"><input type="number" step="0.01" name="price" value="<?= number_format($v['price_paise'] / 100, 2, '.', '') ?>" class="w-20 border border-ink px-1.5 py-1 text-right"></td>
                    <td class="p-2 text-right"><input type="number" step="0.01" name="compare_at_price" value="<?= $v['compare_at_price_paise'] ? number_format($v['compare_at_price_paise'] / 100, 2, '.', '') : '' ?>" class="w-20 border border-ink px-1.5 py-1 text-right"></td>
                    <td class="p-2 text-right"><input type="number" name="stock_quantity" value="<?= (int) $v['stock_quantity'] ?>" class="w-16 border border-ink px-1.5 py-1 text-right"></td>
                    <td class="p-2 text-right"><input type="number" name="low_stock_threshold" value="<?= (int) $v['low_stock_threshold'] ?>" class="w-14 border border-ink px-1.5 py-1 text-right"></td>
                    <td class="p-2 text-center"><input type="radio" name="is_default" value="1" <?= $v['is_default'] ? 'checked' : '' ?>></td>
                    <td class="p-2 whitespace-nowrap">
                      <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-fern hover:underline"><?= icon('check', 'h-3 w-3') ?> Save</button>
                      <button type="submit" formaction="/admin/catalogue/products/<?= (int) $product['id'] ?>/variants/<?= (int) $v['id'] ?>/delete"
                              class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
                    </td>
                  </form>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <details class="mt-4">
          <summary class="cursor-pointer text-sm font-semibold text-leash">Add variant</summary>
          <form method="POST" action="/admin/catalogue/products/<?= (int) $product['id'] ?>/variants" class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?= csrf_field() ?>
            <input type="text" name="label" placeholder="Label, e.g. 5kg" required class="input text-sm">
            <input type="text" name="sku" placeholder="SKU" required class="input text-sm">
            <input type="number" step="0.01" name="price" placeholder="Price ₹" required class="input text-sm">
            <input type="number" name="stock_quantity" placeholder="Stock" class="input text-sm">
            <input type="number" name="weight_grams" placeholder="Weight (g)" class="input text-sm">
            <button type="submit" class="btn btn-secondary btn-sm">Add</button>
          </form>
        </details>
      </div>

      <div class="card-tag p-5 bg-white">
        <p class="font-display font-semibold">Images</p>
        <div class="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-3">
          <?php foreach ($images as $img): ?>
            <div class="relative border-2 border-ink">
              <img src="<?= e(media_url($img['path'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>" loading="lazy" class="w-full aspect-square object-cover">
              <form method="POST" action="/admin/catalogue/products/<?= (int) $product['id'] ?>/images/<?= (int) $img['id'] ?>/delete" class="absolute top-1 right-1">
                <?= csrf_field() ?>
                <button type="submit" class="bg-leash text-paper text-xs px-1.5 py-0.5">&times;</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
        <form method="POST" action="/admin/catalogue/products/<?= (int) $product['id'] ?>/images" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3">
          <?= csrf_field() ?>
          <div>
            <label for="image" class="block text-xs font-semibold mb-1">Image file</label>
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required class="text-sm">
          </div>
          <div>
            <label for="alt_text" class="block text-xs font-semibold mb-1">Alt text</label>
            <input id="alt_text" type="text" name="alt_text" class="input text-sm">
          </div>
          <button type="submit" class="btn btn-secondary btn-sm">Upload</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

<?php \App\Core\View::stop(); ?>
