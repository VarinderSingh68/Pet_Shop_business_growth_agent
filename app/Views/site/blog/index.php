<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $posts */
/** @var array $categories */
/** @var array|null $activeCategory */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center max-w-xl mx-auto" data-reveal>
    <p class="glass inline-flex items-center gap-2 rounded-full text-[#5b21b6] text-xs font-semibold uppercase tracking-widest px-4 py-2">
      <?= icon('document', 'h-3.5 w-3.5') ?> Interactive gallery
    </p>
    <h1 class="font-display mt-5 text-4xl font-bold">Stories from the <span class="text-gradient">store</span></h1>
    <p class="mt-3 text-ink/65">Care tips, product guides, and stories from the store.</p>
  </div>

  <?php if ($categories !== []): ?>
    <div class="mt-8 flex flex-wrap justify-center gap-2" data-reveal>
      <a href="/blog" class="text-sm font-semibold px-4 py-2 rounded-full transition-all duration-200 <?= $activeCategory === null ? 'text-white shadow-lg' : 'glass hover:-translate-y-0.5' ?>" <?= $activeCategory === null ? 'style="background: linear-gradient(135deg, var(--lav), var(--blush));"' : '' ?>>All</a>
      <?php foreach ($categories as $c): ?>
        <a href="/blog?category=<?= e($c['slug']) ?>" class="text-sm font-semibold px-4 py-2 rounded-full transition-all duration-200 <?= ($activeCategory['id'] ?? null) === $c['id'] ? 'text-white shadow-lg' : 'glass hover:-translate-y-0.5' ?>" <?= ($activeCategory['id'] ?? null) === $c['id'] ? 'style="background: linear-gradient(135deg, var(--lav), var(--blush));"' : '' ?>><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($posts === []): ?>
    <p class="mt-10 text-center text-ink/60">No posts yet — check back soon.</p>
  <?php else: ?>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $i => $post): ?>
        <a href="/blog/<?= e($post['slug']) ?>" class="card-tag card-tag--pop p-5 group" data-reveal style="transition-delay:<?= ($i % 6) * 70 ?>ms">
          <?php if ($post['category_name']): ?><div class="card-tag__tab"><?= e($post['category_name']) ?></div><?php endif; ?>
          <h2 class="font-display text-lg font-semibold group-hover:text-[#7c3aed] transition-colors duration-150 <?= $post['category_name'] ? 'mt-6' : '' ?>"><?= e($post['title']) ?></h2>
          <p class="mt-2 text-sm text-ink/60"><?= e($post['excerpt'] ?? '') ?></p>
          <p class="mt-3 text-xs text-ink/40"><?= date('d M Y', strtotime((string) $post['published_at'])) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
