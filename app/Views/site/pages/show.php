<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $page */
/** @var string $body */
?>

<article class="mx-auto max-w-3xl px-4 sm:px-6 py-14">
  <h1 class="font-display text-3xl font-bold"><?= e($page['title']) ?></h1>
  <div class="mt-6 text-ink/80 leading-relaxed whitespace-pre-line"><?= nl2br(e($body)) ?></div>
</article>

<?php \App\Core\View::stop(); ?>
