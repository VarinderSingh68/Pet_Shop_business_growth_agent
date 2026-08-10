<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $testimonials */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/content/blog" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Posts</a>
  <a href="/admin/content/pages" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Pages</a>
  <a href="/admin/content/faqs" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">FAQs</a>
  <a href="/admin/content/testimonials" class="text-sm font-semibold border-b-2 border-ink pb-1">Testimonials</a>
  <a href="/admin/content/banners" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Banners</a>
</div>

<div class="grid lg:grid-cols-[1fr_360px] gap-6">
  <div class="space-y-3">
    <?php foreach ($testimonials as $t): ?>
      <div class="card-tag p-4 bg-white">
        <div class="flex items-start justify-between">
          <div>
            <p class="font-semibold text-sm"><?= e($t['customer_name']) ?> <span class="font-normal text-ink/50">— <?= e($t['pet_description'] ?? '') ?></span></p>
            <p class="text-tennis text-sm"><?= str_repeat('★', (int) $t['rating']) ?></p>
            <p class="text-sm text-ink/75 mt-1">&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
          </div>
          <div class="flex flex-col gap-1 text-xs shrink-0 ml-3">
            <form method="POST" action="/admin/content/testimonials/<?= (int) $t['id'] ?>/toggle">
              <?= csrf_field() ?>
              <button type="submit" class="font-semibold text-fern hover:underline"><?= $t['is_published'] ? 'Unpublish' : 'Publish' ?></button>
            </form>
            <form method="POST" action="/admin/content/testimonials/<?= (int) $t['id'] ?>/delete">
              <?= csrf_field() ?>
              <button type="submit" class="inline-flex items-center gap-1 font-semibold text-leash hover:underline"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if ($testimonials === []): ?><p class="text-ink/50 text-sm">No testimonials yet.</p><?php endif; ?>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add testimonial</p>
    <form method="POST" action="/admin/content/testimonials" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="customer_name" placeholder="Customer name" required class="input text-sm">
      <input type="text" name="pet_description" placeholder="Pet, e.g. Milo, Labrador" class="input text-sm">
      <textarea name="quote" rows="3" placeholder="Quote" required class="input text-sm"></textarea>
      <select name="rating" class="input text-sm">
        <?php for ($r = 5; $r >= 1; $r--): ?><option value="<?= $r ?>"><?= $r ?> stars</option><?php endfor; ?>
      </select>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
