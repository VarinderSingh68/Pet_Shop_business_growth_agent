<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $columns */
?>

<div class="max-w-xl space-y-6">
  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">CSV format</p>
    <p class="mt-1 text-sm text-ink/60">First row must be a header with these columns, in any order:</p>
    <div class="mt-3 flex flex-wrap gap-1.5">
      <?php foreach ($columns as $col): ?>
        <code class="text-xs bg-mist/50 px-2 py-1 font-mono"><?= e($col) ?></code>
      <?php endforeach; ?>
    </div>
    <ul class="mt-4 text-sm text-ink/60 list-disc list-inside space-y-1">
      <li><strong>name, category, pet_type, life_stage, sku, price</strong> are required.</li>
      <li><strong>brand</strong> and <strong>stock_quantity</strong> are optional.</li>
      <li><code>category</code> and <code>brand</code> must match an existing name exactly (case-insensitive) — this doesn't create new categories or brands.</li>
      <li><code>pet_type</code>: dog, cat, bird, fish, small_pet, or other.</li>
      <li><code>life_stage</code>: puppy_kitten, adult, senior, or all.</li>
      <li>Each row becomes a product with one "Standard" variant. Products import as drafts — publish them from the catalogue once you've checked them over.</li>
    </ul>
  </div>

  <form method="POST" action="/admin/catalogue/import" enctype="multipart/form-data" class="card-tag p-5 bg-white space-y-4">
    <?= csrf_field() ?>
    <div>
      <label for="csv" class="block text-sm font-semibold mb-1">CSV file</label>
      <input type="file" id="csv" name="csv" accept=".csv,text/csv" required class="input text-sm">
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><?= icon('upload', 'h-4 w-4') ?> Import</button>
  </form>
</div>

<?php \App\Core\View::stop(); ?>
