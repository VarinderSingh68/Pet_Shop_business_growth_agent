<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $page */
/** @var string $body */
?>

<article class="mx-auto max-w-3xl px-4 sm:px-6 py-14">
  <h1 class="font-display text-3xl font-bold"><?= e($page['title']) ?></h1>
  <?php /* Raw output is safe here: body is staff-authored via the admin rich-text editor and passes through HtmlSanitizer::clean() before it's ever saved. */ ?>
  <div class="mt-6 prose-content text-ink/80 leading-relaxed"><?= $body ?></div>
</article>

<?php \App\Core\View::stop(); ?>
