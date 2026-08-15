<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $comments */
/** @var string $status */
/** @var array $countsByStatus */

$tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'flagged' => 'Flagged'];
?>

<div class="flex items-center justify-between mb-4">
  <div class="flex gap-2">
    <a href="/admin/content/blog" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Posts</a>
    <a href="/admin/content/blog/categories" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Categories</a>
    <a href="/admin/content/blog/comments" class="text-sm font-semibold border-b-2 border-ink pb-1">Comments</a>
    <a href="/admin/content/pages" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Pages</a>
    <a href="/admin/content/faqs" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">FAQs</a>
    <a href="/admin/content/testimonials" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Testimonials</a>
    <a href="/admin/content/banners" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Banners</a>
  </div>
</div>

<div class="flex gap-2 mb-4">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="/admin/content/blog/comments?status=<?= $key ?>" class="text-sm font-semibold pb-1 <?= $status === $key ? 'border-b-2 border-leash text-leash' : 'text-ink/50 hover:text-ink' ?>">
      <?= e($label) ?> <span class="text-ink/40">(<?= (int) ($countsByStatus[$key] ?? 0) ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead>
      <tr class="border-b-2 border-ink">
        <th class="p-2 text-left">Post</th><th class="p-2 text-left">Commenter</th><th class="p-2 text-left">Comment</th>
        <th class="p-2 text-left">Date</th><th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($comments as $c): ?>
        <tr class="border-b border-mist align-top">
          <td class="p-2"><a href="/blog/<?= e($c['post_slug']) ?>" target="_blank" class="font-medium hover:text-leash"><?= e($c['post_title']) ?></a></td>
          <td class="p-2"><?= e($c['name']) ?><br><span class="text-xs text-ink/50"><?= e($c['email']) ?></span></td>
          <td class="p-2 max-w-md"><?= e($c['body']) ?></td>
          <td class="p-2 text-ink/50 whitespace-nowrap"><?= e(date('d M Y', strtotime((string) $c['created_at']))) ?></td>
          <td class="p-2 whitespace-nowrap space-y-1">
            <?php if ($c['status'] !== 'approved'): ?>
              <form method="POST" action="/admin/content/blog/comments/<?= (int) $c['id'] ?>/status">
                <?= csrf_field() ?><input type="hidden" name="status" value="approved">
                <button type="submit" class="block text-xs font-semibold text-fern hover:underline">Approve</button>
              </form>
            <?php endif; ?>
            <?php if ($c['status'] !== 'flagged'): ?>
              <form method="POST" action="/admin/content/blog/comments/<?= (int) $c['id'] ?>/status">
                <?= csrf_field() ?><input type="hidden" name="status" value="flagged">
                <button type="submit" class="block text-xs font-semibold text-tennis hover:underline">Flag</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="/admin/content/blog/comments/<?= (int) $c['id'] ?>/delete" onsubmit="return confirm('Delete this comment?');">
              <?= csrf_field() ?>
              <button type="submit" class="block text-xs font-semibold text-leash hover:underline">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($comments === []): ?><tr><td colspan="5" class="p-6 text-center text-ink/50">Nothing here.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
