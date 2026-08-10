<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $customers */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var string $search */

$totalPages = max(1, (int) ceil($total / $perPage));
?>

<form method="GET" action="/admin/customers" class="flex flex-wrap gap-2 mb-4">
  <div class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'h-4 w-4') ?></span>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, or phone" class="input pl-9 !w-auto min-w-[240px]">
  </div>
  <button type="submit" class="btn btn-secondary"><?= icon('search', 'h-4 w-4') ?> Search</button>
</form>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">Name</th><th class="p-2 text-left">Email</th><th class="p-2 text-left">Joined</th>
        <th class="p-2 text-right">Orders</th><th class="p-2 text-right">Lifetime value</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customers as $c): ?>
        <tr class="border-b border-mist cursor-pointer hover:bg-tennis/10 transition-colors duration-150" onclick="window.location='/admin/customers/<?= (int) $c['id'] ?>'">
          <td class="p-2 font-medium">
            <span class="flex items-center gap-2.5">
              <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-paper text-xs font-bold"><?= e(mb_strtoupper(mb_substr((string) $c['name'], 0, 1))) ?></span>
              <?= e($c['name']) ?>
            </span>
          </td>
          <td class="p-2 text-ink/60"><?= e($c['email']) ?></td>
          <td class="p-2 text-ink/60"><?= e(date('d M Y', strtotime((string) $c['created_at']))) ?></td>
          <td class="p-2 text-right"><?= (int) $c['order_count'] ?></td>
          <td class="p-2 text-right font-semibold"><?= money((int) $c['lifetime_value_paise']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($customers === []): ?>
        <tr><td colspan="5" class="p-6 text-center text-ink/50">No customers match.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-4 flex gap-2" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>" class="w-8 h-8 flex items-center justify-center border-2 text-sm transition-colors duration-150 <?= $i === $page ? 'border-leash bg-leash text-paper' : 'border-ink hover:border-leash' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php \App\Core\View::stop(); ?>
