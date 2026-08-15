<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $pages */
?>

<div class="flex items-center justify-between mb-4">
  <div class="flex gap-2">
    <a href="/admin/content/blog" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Posts</a>
    <a href="/admin/content/blog/categories" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Categories</a>
    <a href="/admin/content/blog/comments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Comments</a>
    <a href="/admin/content/pages" class="text-sm font-semibold border-b-2 border-ink pb-1">Pages</a>
    <a href="/admin/content/faqs" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">FAQs</a>
    <a href="/admin/content/testimonials" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Testimonials</a>
    <a href="/admin/content/banners" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Banners</a>
  </div>
  <a href="/admin/content/pages/create" class="btn btn-primary btn-sm">New page</a>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Title</th><th class="p-2 text-left">Slug</th><th class="p-2 text-left">Published</th><th class="p-2"></th></tr></thead>
    <tbody>
      <?php foreach ($pages as $p): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($p['title']) ?></td>
          <td class="p-2 text-ink/50">/legal/<?= e($p['slug']) ?></td>
          <td class="p-2"><?= $p['is_published'] ? 'Yes' : 'No' ?></td>
          <td class="p-2 whitespace-nowrap">
            <a href="/admin/content/pages/<?= (int) $p['id'] ?>/edit" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline"><?= icon('edit', 'h-3 w-3') ?> Edit</a>
            <form method="POST" action="/admin/content/pages/<?= (int) $p['id'] ?>/delete" class="inline" onsubmit="return confirm('Delete this page?');">
              <?= csrf_field() ?>
              <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-ink/50 hover:text-leash ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($pages === []): ?><tr><td colspan="4" class="p-6 text-center text-ink/50">No pages yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
