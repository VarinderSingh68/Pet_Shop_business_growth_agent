<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $services */

$categoryLabels = ['grooming' => 'Grooming', 'boarding' => 'Boarding', 'vet' => 'Vet care', 'training' => 'Training'];
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-14">
  <h1 class="font-display text-3xl font-bold">Services</h1>
  <p class="mt-2 text-ink/60 max-w-2xl">Book a real appointment slot with our team — grooming, boarding, vet visits, and training.</p>

  <?php if ($services === []): ?>
    <p class="mt-8 text-ink/60">No services are available to book right now.</p>
  <?php else: ?>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($services as $service): ?>
        <a href="/services/<?= e($service['slug']) ?>" class="card-tag card-tag--pop p-6 group hover:bg-ink hover:text-paper transition-colors duration-150">
          <div class="card-tag__tab"><?= e($categoryLabels[$service['category']] ?? $service['category']) ?></div>
          <span class="icon-chip icon-chip-info group-hover:!bg-paper/15 group-hover:!text-paper"><?= icon('calendar', 'h-4 w-4') ?></span>
          <p class="font-display text-lg font-semibold mt-3"><?= e($service['name']) ?></p>
          <p class="text-sm mt-2 opacity-70"><?= e($service['description']) ?></p>
          <div class="mt-4 flex items-center justify-between text-sm">
            <span><?= (int) $service['duration_minutes'] ?> min</span>
            <span class="font-display font-bold"><?= money((int) $service['price_paise']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
