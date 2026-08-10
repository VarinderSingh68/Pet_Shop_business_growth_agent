<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $segment */
/** @var array $members */
?>

<a href="/admin/marketing/segments" class="text-sm text-ink/50 hover:text-leash">&larr; Segments</a>
<h1 class="font-display text-xl font-semibold mt-2"><?= e($segment['name']) ?></h1>
<p class="text-sm text-ink/60"><?= e($segment['description']) ?></p>

<div class="mt-4 border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Email</th></tr></thead>
    <tbody>
      <?php foreach ($members as $m): ?>
        <tr class="border-b border-mist">
          <td class="p-2"><a href="/admin/customers/<?= (int) $m['id'] ?>" class="hover:text-leash"><?= e($m['name']) ?></a></td>
          <td class="p-2 text-ink/60"><?= e($m['email']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($members === []): ?>
        <tr><td colspan="2" class="p-6 text-center text-ink/50">No members right now.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
