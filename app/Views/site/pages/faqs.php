<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $faqs */
?>

<section class="mx-auto max-w-3xl px-4 sm:px-6 py-14" x-data="{ open: null }">
  <h1 class="font-display text-3xl font-bold">Frequently asked questions</h1>

  <?php if ($faqs === []): ?>
    <p class="mt-6 text-ink/60">No FAQs published yet.</p>
  <?php else: ?>
    <div class="mt-8 space-y-3">
      <?php foreach ($faqs as $i => $faq): ?>
        <div class="border-2 border-ink">
          <button type="button" @click="open = open === <?= $i ?> ? null : <?= $i ?>"
                  class="w-full flex items-center justify-between px-4 py-3 text-left font-semibold">
            <?= e($faq['question']) ?>
            <span x-text="open === <?= $i ?> ? '−' : '+'" aria-hidden="true"></span>
          </button>
          <div x-show="open === <?= $i ?>" x-cloak class="px-4 pb-4 text-sm text-ink/75"><?= nl2br(e($faq['answer'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
