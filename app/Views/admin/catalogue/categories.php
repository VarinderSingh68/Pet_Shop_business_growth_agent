<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $categories */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/catalogue" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Products</a>
  <a href="/admin/catalogue/categories" class="text-sm font-semibold border-b-2 border-ink pb-1">Categories</a>
  <a href="/admin/catalogue/brands" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Brands</a>
  <a href="/admin/catalogue/reviews" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Reviews</a>
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Parent</th><th class="p-2 text-left">Meta title</th><th class="p-2 text-left">Meta description</th><th class="p-2 text-left">Active</th><th class="p-2"></th></tr></thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
          <tr class="border-b border-mist">
            <form method="POST" action="/admin/catalogue/categories/<?= (int) $c['id'] ?>">
              <?= csrf_field() ?>
              <td class="p-2"><input type="text" name="name" value="<?= e($c['name']) ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2">
                <select name="parent_id" class="border border-ink px-1.5 py-1">
                  <option value="">None</option>
                  <?php foreach ($categories as $p): ?>
                    <?php if ($p['id'] === $c['id']) continue; ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $c['parent_id'] === $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="p-2"><input type="text" name="meta_title" value="<?= e($c['meta_title'] ?? '') ?>" placeholder="Defaults to name" class="border border-ink px-1.5 py-1 w-44"></td>
              <td class="p-2"><input type="text" name="meta_description" value="<?= e($c['meta_description'] ?? '') ?>" placeholder="Defaults to description" class="border border-ink px-1.5 py-1 w-56"></td>
              <td class="p-2"><input type="checkbox" name="is_active" value="1" <?= $c['is_active'] ? 'checked' : '' ?>></td>
              <td class="p-2 whitespace-nowrap">
                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-fern hover:underline"><?= icon('check', 'h-3 w-3') ?> Save</button>
                <button type="submit" formaction="/admin/catalogue/categories/<?= (int) $c['id'] ?>/delete" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add category</p>
    <form method="POST" action="/admin/catalogue/categories" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Name" required class="input text-sm">
      <select name="parent_id" class="input text-sm">
        <option value="">No parent (top level)</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
