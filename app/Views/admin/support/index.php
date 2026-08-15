<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $enquiries */
/** @var string $status */
/** @var array $countsByStatus */

$tabs = ['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'];
?>

<div class="flex gap-2 mb-4">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="/admin/support?status=<?= $key ?>" class="text-sm font-semibold pb-1 <?= $status === $key ? 'border-b-2 border-ink' : 'text-ink/50 hover:text-ink' ?>">
      <?= e($label) ?> <span class="text-ink/40">(<?= (int) ($countsByStatus[$key] ?? 0) ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">From</th><th class="p-2 text-left">Subject</th><th class="p-2 text-left">Order</th>
        <th class="p-2 text-left">Received</th><th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($enquiries as $e): ?>
        <tr class="border-b border-mist">
          <td class="p-2">
            <p class="font-medium"><?= e($e['name']) ?></p>
            <p class="text-xs text-ink/50"><?= e($e['email']) ?></p>
          </td>
          <td class="p-2"><?= e($e['subject']) ?></td>
          <td class="p-2"><?= $e['order_number'] ? e($e['order_number']) : '—' ?></td>
          <td class="p-2 text-ink/50"><?= e(date('d M Y, g:i A', strtotime((string) $e['created_at']))) ?></td>
          <td class="p-2"><a href="/admin/support/<?= (int) $e['id'] ?>" class="text-xs font-semibold text-leash hover:underline">View &rarr;</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($enquiries === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">Nothing here.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
