<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $products */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var string $search */
/** @var string $status */

$totalPages = max(1, (int) ceil($total / $perPage));
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-4">
  <div class="flex gap-2">
    <a href="/admin/catalogue" class="text-sm font-semibold border-b-2 border-leash text-leash pb-1">Products</a>
    <a href="/admin/catalogue/categories" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1 border-b-2 border-transparent">Categories</a>
    <a href="/admin/catalogue/brands" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1 border-b-2 border-transparent">Brands</a>
  </div>
  <div class="flex gap-2">
    <a href="/admin/catalogue/import" class="btn btn-secondary btn-sm"><?= icon('upload', 'h-4 w-4') ?> Import CSV</a>
    <a href="/admin/catalogue/products/create" class="btn btn-primary btn-sm"><?= icon('plus', 'h-4 w-4') ?> Add product</a>
  </div>
</div>

<form method="GET" action="/admin/catalogue" class="flex flex-wrap gap-2 mb-4">
  <div class="relative flex-1 min-w-[200px]">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'h-4 w-4') ?></span>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name or SKU" class="input pl-9">
  </div>
  <select name="status" class="input !w-auto">
    <option value="">All statuses</option>
    <?php foreach (['draft', 'active', 'archived'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-secondary"><?= icon('filter', 'h-4 w-4') ?> Filter</button>
</form>

<form method="POST" action="/admin/catalogue/bulk">
  <?= csrf_field() ?>
  <div class="flex items-center gap-2 mb-2">
    <select name="bulk_action" class="input !w-auto py-1.5">
      <option value="">Bulk action…</option>
      <option value="activate">Activate</option>
      <option value="archive">Archive</option>
      <option value="delete">Delete</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  </div>

  <div class="overflow-x-auto border-2 border-ink bg-white">
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="border-b-2 border-ink">
          <th class="p-2 w-8"><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
          <th class="p-2 text-left">Product</th>
          <th class="p-2 text-left">Category</th>
          <th class="p-2 text-left">Brand</th>
          <th class="p-2 text-left">Pet</th>
          <th class="p-2 text-right">Variants</th>
          <th class="p-2 text-right">Stock</th>
          <th class="p-2 text-left">Status</th>
          <th class="p-2"></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $productStatusBadge = ['active' => 'badge-success', 'draft' => 'badge-neutral', 'archived' => 'badge-danger'];
        ?>
        <?php foreach ($products as $p): ?>
          <tr class="border-b border-mist hover:bg-mist/20 transition-colors duration-150">
            <td class="p-2"><input class="row-check" type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>"></td>
            <td class="p-2 font-medium"><?= e($p['name']) ?></td>
            <td class="p-2"><?= e($p['category_name'] ?? '—') ?></td>
            <td class="p-2"><?= e($p['brand_name'] ?? '—') ?></td>
            <td class="p-2 capitalize"><?= e(str_replace('_', ' ', $p['pet_type'])) ?></td>
            <td class="p-2 text-right"><?= (int) $p['variant_count'] ?></td>
            <td class="p-2 text-right">
              <?php if ((int) $p['total_stock'] === 0): ?>
                <span class="badge badge-danger"><?= icon('alert-triangle', 'h-3 w-3') ?> 0</span>
              <?php else: ?>
                <?= (int) $p['total_stock'] ?>
              <?php endif; ?>
            </td>
            <td class="p-2">
              <span class="badge <?= $productStatusBadge[$p['status']] ?? 'badge-neutral' ?>"><?= ucfirst($p['status']) ?></span>
            </td>
            <td class="p-2 text-right">
              <a href="/admin/catalogue/products/<?= (int) $p['id'] ?>/edit" class="inline-flex items-center gap-1 text-leash font-semibold hover:underline"><?= icon('edit', 'h-3.5 w-3.5') ?> Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($products === []): ?>
          <tr><td colspan="9" class="p-6 text-center text-ink/50">No products match your filters.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if ($totalPages > 1): ?>
  <nav class="mt-4 flex gap-2" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
         class="w-8 h-8 flex items-center justify-center border-2 text-sm <?= $i === $page ? 'border-leash bg-leash text-paper' : 'border-ink' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php \App\Core\View::stop(); ?>
