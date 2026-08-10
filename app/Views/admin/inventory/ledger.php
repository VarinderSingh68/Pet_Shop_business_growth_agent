<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $variant */
/** @var array $movements */
?>

<a href="/admin/inventory" class="text-sm text-ink/50 hover:text-leash">&larr; Inventory</a>
<h1 class="font-display text-xl font-semibold mt-2"><?= e($variant['product_name']) ?> — <?= e($variant['label']) ?></h1>
<p class="text-sm text-ink/50">SKU <?= e($variant['sku']) ?> &middot; Current stock: <strong><?= (int) $variant['stock_quantity'] ?></strong></p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">Date</th><th class="p-2 text-left">Change</th><th class="p-2 text-left">Reason</th>
        <th class="p-2 text-left">Reference</th><th class="p-2 text-left">Note</th><th class="p-2 text-left">By</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($movements as $m): ?>
        <tr class="border-b border-mist">
          <td class="p-2"><?= e($m['created_at']) ?></td>
          <td class="p-2 font-semibold <?= (int) $m['change_quantity'] < 0 ? 'text-leash' : 'text-fern' ?>">
            <?= (int) $m['change_quantity'] > 0 ? '+' : '' ?><?= (int) $m['change_quantity'] ?>
          </td>
          <td class="p-2 capitalize"><?= e(str_replace('_', ' ', $m['reason'])) ?></td>
          <td class="p-2 text-ink/50"><?= e($m['reference_type'] ? $m['reference_type'] . ' #' . $m['reference_id'] : '—') ?></td>
          <td class="p-2 text-ink/60"><?= e($m['note'] ?? '') ?></td>
          <td class="p-2"><?= e($m['user_name'] ?? 'System') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($movements === []): ?>
        <tr><td colspan="6" class="p-6 text-center text-ink/50">No movements recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
