<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $reviews */
/** @var string $status */
/** @var array $countsByStatus */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */

$tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'flagged' => 'Flagged'];
$totalPages = max(1, (int) ceil($total / $perPage));
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-4">
  <div class="flex gap-2">
    <a href="/admin/catalogue" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Products</a>
    <a href="/admin/catalogue/categories" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Categories</a>
    <a href="/admin/catalogue/brands" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Brands</a>
    <a href="/admin/catalogue/reviews" class="text-sm font-semibold border-b-2 border-ink pb-1">Reviews</a>
  </div>
</div>

<div class="flex gap-2 mb-4">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="/admin/catalogue/reviews?status=<?= $key ?>" class="text-sm font-semibold pb-1 <?= $status === $key ? 'border-b-2 border-leash text-leash' : 'text-ink/50 hover:text-ink' ?>">
      <?= e($label) ?> <span class="text-ink/40">(<?= (int) ($countsByStatus[$key] ?? 0) ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">Product</th><th class="p-2 text-left">Reviewer</th><th class="p-2 text-left">Rating</th>
        <th class="p-2 text-left">Review</th><th class="p-2 text-left">Date</th><th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reviews as $r): ?>
        <tr class="border-b border-mist align-top">
          <td class="p-2"><a href="/shop/<?= e($r['product_slug']) ?>" target="_blank" class="font-medium hover:text-leash"><?= e($r['product_name']) ?></a></td>
          <td class="p-2"><?= e($r['reviewer_name'] ?? 'Guest') ?><?= $r['is_verified_purchase'] ? ' <span class="text-fern text-xs">(verified)</span>' : '' ?></td>
          <td class="p-2"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></td>
          <td class="p-2 max-w-md">
            <?php if ($r['title']): ?><p class="font-semibold"><?= e($r['title']) ?></p><?php endif; ?>
            <p class="text-ink/70"><?= e($r['body'] ?? '') ?></p>
            <?php if ($r['flagged_reason']): ?><p class="text-xs text-leash mt-1">Flagged: <?= e($r['flagged_reason']) ?></p><?php endif; ?>
          </td>
          <td class="p-2 text-ink/50 whitespace-nowrap"><?= e(date('d M Y', strtotime((string) $r['created_at']))) ?></td>
          <td class="p-2 whitespace-nowrap space-y-1">
            <?php if ($r['status'] !== 'approved'): ?>
              <form method="POST" action="/admin/catalogue/reviews/<?= (int) $r['id'] ?>/status">
                <?= csrf_field() ?><input type="hidden" name="status" value="approved">
                <button type="submit" class="block text-xs font-semibold text-fern hover:underline">Approve</button>
              </form>
            <?php endif; ?>
            <?php if ($r['status'] !== 'flagged'): ?>
              <form method="POST" action="/admin/catalogue/reviews/<?= (int) $r['id'] ?>/status">
                <?= csrf_field() ?><input type="hidden" name="status" value="flagged">
                <button type="submit" class="block text-xs font-semibold text-tennis hover:underline">Flag</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="/admin/catalogue/reviews/<?= (int) $r['id'] ?>/delete" onsubmit="return confirm('Delete this review?');">
              <?= csrf_field() ?>
              <button type="submit" class="block text-xs font-semibold text-leash hover:underline">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($reviews === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">Nothing here.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::include('components/admin-pagination', ['page' => $page, 'totalPages' => $totalPages, 'basePath' => '/admin/catalogue/reviews', 'query' => ['status' => $status]]); ?>

<?php \App\Core\View::stop(); ?>
