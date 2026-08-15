<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $banners */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/content/blog" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Posts</a>
  <a href="/admin/content/blog/comments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Comments</a>
  <a href="/admin/content/pages" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Pages</a>
  <a href="/admin/content/faqs" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">FAQs</a>
  <a href="/admin/content/testimonials" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Testimonials</a>
  <a href="/admin/content/banners" class="text-sm font-semibold border-b-2 border-ink pb-1">Banners</a>
</div>

<div class="grid lg:grid-cols-[1fr_360px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Title</th><th class="p-2 text-left">Type</th><th class="p-2 text-left">Page</th><th class="p-2 text-left">Active</th><th class="p-2"></th></tr></thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
          <tr class="border-b border-mist">
            <td class="p-2 font-medium"><?= e($b['title']) ?></td>
            <td class="p-2 capitalize"><?= e(str_replace('_', ' ', $b['display_type'])) ?></td>
            <td class="p-2 capitalize"><?= e($b['target_page']) ?></td>
            <td class="p-2"><?= $b['is_active'] ? 'Yes' : 'No' ?></td>
            <td class="p-2 whitespace-nowrap">
              <form method="POST" action="/admin/content/banners/<?= (int) $b['id'] ?>/toggle" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs font-semibold text-fern hover:underline"><?= $b['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
              <form method="POST" action="/admin/content/banners/<?= (int) $b['id'] ?>/delete" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($banners === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">No banners yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add banner</p>
    <form method="POST" action="/admin/content/banners" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="title" placeholder="Title" required class="input text-sm">
      <input type="text" name="body" placeholder="Body (optional)" class="input text-sm">
      <input type="text" name="link_url" placeholder="Link URL (optional)" class="input text-sm">
      <input type="text" name="link_label" placeholder="Link label (optional)" class="input text-sm">
      <select name="display_type" class="input text-sm">
        <option value="top_bar">Top bar</option>
        <option value="popup">Popup</option>
      </select>
      <select name="target_page" class="input text-sm">
        <option value="all">All pages</option>
        <option value="home">Homepage only</option>
        <option value="shop">Shop only</option>
      </select>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
