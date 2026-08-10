<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $posts */
/** @var array $categories */
/** @var array|null $activeCategory */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-14">
  <h1 class="font-display text-3xl font-bold">Blog</h1>
  <p class="mt-2 text-ink/60">Care tips, product guides, and stories from the store.</p>

  <?php if ($categories !== []): ?>
    <div class="mt-6 flex flex-wrap gap-2">
      <a href="/blog" class="text-sm font-semibold px-3 py-1.5 border-2 <?= $activeCategory === null ? 'border-leash bg-leash text-paper' : 'border-ink' ?>">All</a>
      <?php foreach ($categories as $c): ?>
        <a href="/blog?category=<?= e($c['slug']) ?>" class="text-sm font-semibold px-3 py-1.5 border-2 <?= ($activeCategory['id'] ?? null) === $c['id'] ? 'border-leash bg-leash text-paper' : 'border-ink' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($posts === []): ?>
    <p class="mt-10 text-ink/60">No posts yet — check back soon.</p>
  <?php else: ?>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $post): ?>
        <a href="/blog/<?= e($post['slug']) ?>" class="card-tag p-5 group">
          <?php if ($post['category_name']): ?><div class="card-tag__tab"><?= e($post['category_name']) ?></div><?php endif; ?>
          <h2 class="font-display text-lg font-semibold group-hover:text-leash"><?= e($post['title']) ?></h2>
          <p class="mt-2 text-sm text-ink/60"><?= e($post['excerpt'] ?? '') ?></p>
          <p class="mt-3 text-xs text-ink/40"><?= date('d M Y', strtotime((string) $post['published_at'])) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
