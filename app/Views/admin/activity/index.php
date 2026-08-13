<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $entries */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var string $search */
/** @var int $userId */
/** @var array $staffUsers */

$totalPages = max(1, (int) ceil($total / $perPage));
?>

<form method="GET" action="/admin/activity" class="flex flex-wrap gap-2 mb-4">
  <div class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'h-4 w-4') ?></span>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Action or description" class="input pl-9 !w-auto min-w-[220px]">
  </div>
  <select name="user_id" class="input !w-auto">
    <option value="0">All staff</option>
    <?php foreach ($staffUsers as $u): ?>
      <option value="<?= (int) $u['id'] ?>" <?= $userId === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-secondary"><?= icon('filter', 'h-4 w-4') ?> Filter</button>
</form>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">When</th><th class="p-2 text-left">Who</th><th class="p-2 text-left">Action</th>
        <th class="p-2 text-left">Description</th><th class="p-2 text-left">IP</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entries as $entry): ?>
        <tr class="border-b border-mist">
          <td class="p-2 text-ink/60 whitespace-nowrap"><?= e(date('d M Y, H:i', strtotime((string) $entry['created_at']))) ?></td>
          <td class="p-2"><?= e($entry['user_name'] ?? 'System') ?></td>
          <td class="p-2"><span class="badge badge-info"><?= e($entry['action']) ?></span></td>
          <td class="p-2"><?= e($entry['description'] ?? '') ?></td>
          <td class="p-2 text-ink/50"><?= e($entry['ip_address'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($entries === []): ?>
        <tr><td colspan="5" class="p-6 text-center text-ink/50">No activity matches.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-4 flex gap-2" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&user_id=<?= $userId ?>"
         class="w-8 h-8 flex items-center justify-center border-2 text-sm transition-colors duration-150 <?= $i === $page ? 'border-leash bg-leash text-paper' : 'border-ink hover:border-leash' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php \App\Core\View::stop(); ?>
