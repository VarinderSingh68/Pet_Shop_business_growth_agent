<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $staff */
?>

<div class="flex items-center justify-between mb-4">
  <div class="flex gap-2">
    <a href="/admin/services/staff" class="text-sm font-semibold border-b-2 border-ink pb-1">Staff</a>
    <a href="/admin/services/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Services</a>
    <a href="/admin/services/appointments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Appointments</a>
  </div>
  <a href="/admin/services/staff/create" class="btn btn-primary btn-sm">Add staff member</a>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Title</th><th class="p-2 text-left">Active</th><th class="p-2 text-right">No-shows</th><th class="p-2"></th></tr></thead>
    <tbody>
      <?php foreach ($staff as $s): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($s['name']) ?></td>
          <td class="p-2 text-ink/60"><?= e($s['title'] ?? '—') ?></td>
          <td class="p-2"><?= $s['is_active'] ? 'Yes' : 'No' ?></td>
          <td class="p-2 text-right <?= (int) $s['no_show_count'] > 0 ? 'text-leash font-semibold' : '' ?>"><?= (int) $s['no_show_count'] ?></td>
          <td class="p-2"><a href="/admin/services/staff/<?= (int) $s['id'] ?>/edit" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline"><?= icon('edit', 'h-3 w-3') ?> Edit</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($staff === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">No staff yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
