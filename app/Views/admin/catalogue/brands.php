<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $brands */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/catalogue" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Products</a>
  <a href="/admin/catalogue/categories" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Categories</a>
  <a href="/admin/catalogue/brands" class="text-sm font-semibold border-b-2 border-ink pb-1">Brands</a>
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Description</th><th class="p-2"></th></tr></thead>
      <tbody>
        <?php foreach ($brands as $b): ?>
          <tr class="border-b border-mist">
            <form method="POST" action="/admin/catalogue/brands/<?= (int) $b['id'] ?>">
              <?= csrf_field() ?>
              <td class="p-2"><input type="text" name="name" value="<?= e($b['name']) ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2"><input type="text" name="description" value="<?= e($b['description'] ?? '') ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2 whitespace-nowrap">
                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-fern hover:underline"><?= icon('check', 'h-3 w-3') ?> Save</button>
                <button type="submit" formaction="/admin/catalogue/brands/<?= (int) $b['id'] ?>/delete" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add brand</p>
    <form method="POST" action="/admin/catalogue/brands" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Name" required class="input text-sm">
      <input type="text" name="description" placeholder="Description" class="input text-sm">
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
