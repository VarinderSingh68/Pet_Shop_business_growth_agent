<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $faqs */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/content/blog" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Posts</a>
  <a href="/admin/content/blog/comments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Comments</a>
  <a href="/admin/content/pages" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Pages</a>
  <a href="/admin/content/faqs" class="text-sm font-semibold border-b-2 border-ink pb-1">FAQs</a>
  <a href="/admin/content/testimonials" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Testimonials</a>
  <a href="/admin/content/banners" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Banners</a>
</div>

<div class="grid lg:grid-cols-[1fr_360px] gap-6">
  <div class="space-y-3">
    <?php foreach ($faqs as $f): ?>
      <form method="POST" action="/admin/content/faqs/<?= (int) $f['id'] ?>" class="card-tag p-4 bg-white space-y-2">
        <?= csrf_field() ?>
        <input type="text" name="question" value="<?= e($f['question']) ?>" class="font-semibold input text-sm">
        <textarea name="answer" rows="2" class="input text-sm"><?= e($f['answer']) ?></textarea>
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-xs">
            <input type="checkbox" name="is_published" value="1" <?= $f['is_published'] ? 'checked' : '' ?> class="border-2 border-ink">
            Published
          </label>
          <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-fern hover:underline"><?= icon('check', 'h-3 w-3') ?> Save</button>
            <button type="submit" formaction="/admin/content/faqs/<?= (int) $f['id'] ?>/delete" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
          </div>
        </div>
      </form>
    <?php endforeach; ?>
    <?php if ($faqs === []): ?><p class="text-ink/50 text-sm">No FAQs yet.</p><?php endif; ?>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add FAQ</p>
    <form method="POST" action="/admin/content/faqs" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="question" placeholder="Question" required class="input text-sm">
      <textarea name="answer" rows="3" placeholder="Answer" required class="input text-sm"></textarea>
      <input type="number" name="sort_order" placeholder="Sort order" class="input text-sm">
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
