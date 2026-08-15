<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $post */
/** @var array $comments */
?>

<article class="mx-auto max-w-3xl px-4 sm:px-6 py-14">
  <a href="/blog" class="text-sm text-ink/50 hover:text-leash">&larr; Blog</a>
  <?php if ($post['category_name']): ?>
    <p class="mt-4 text-xs uppercase tracking-wide text-leash font-semibold"><?= e($post['category_name']) ?></p>
  <?php endif; ?>
  <h1 class="font-display text-3xl sm:text-4xl font-bold mt-2"><?= e($post['title']) ?></h1>
  <p class="mt-2 text-sm text-ink/50">
    <?= date('d M Y', strtotime((string) $post['published_at'])) ?><?= $post['author_name'] ? ' · ' . e($post['author_name']) : '' ?>
  </p>

  <?php if (!empty($post['cover_image_path'])): ?>
    <div class="mt-6 aspect-[16/9] overflow-hidden card-tag">
      <img src="<?= e(media_url($post['cover_image_path'])) ?>" alt="" class="w-full h-full object-cover">
    </div>
  <?php endif; ?>

  <?php /* Raw output is safe here: body is staff-authored via the admin rich-text editor and passes through HtmlSanitizer::clean() before it's ever saved. */ ?>
  <div class="mt-8 prose-content text-ink/80 leading-relaxed"><?= $post['body'] ?></div>

  <div class="mt-16 border-t-2 border-ink pt-8">
    <h2 class="font-display text-xl font-bold">Comments</h2>

    <?php if ($comments === []): ?>
      <p class="mt-3 text-sm text-ink/60">No comments yet.</p>
    <?php else: ?>
      <ul class="mt-4 space-y-4">
        <?php foreach ($comments as $c): ?>
          <li class="card-tag p-4">
            <p class="text-sm font-semibold"><?= e($c['name']) ?></p>
            <p class="text-sm text-ink/75 mt-1"><?= e($c['body']) ?></p>
            <p class="text-xs text-ink/40 mt-2"><?= e($c['created_at']) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form method="POST" action="/blog/<?= (int) $post['id'] ?>/comments" class="mt-6 max-w-lg space-y-3">
      <?= csrf_field() ?>
      <div class="grid sm:grid-cols-2 gap-3">
        <input type="text" name="name" placeholder="Your name" required value="<?= e(old('name')) ?>" class="input text-sm">
        <input type="email" name="email" placeholder="Email (not published)" required value="<?= e(old('email')) ?>" class="input text-sm">
      </div>
      <textarea name="body" rows="3" placeholder="Add a comment" required class="input text-sm"></textarea>
      <button type="submit" class="btn btn-primary">Post comment</button>
    </form>
  </div>
</article>

<?php \App\Core\View::stop(); ?>
