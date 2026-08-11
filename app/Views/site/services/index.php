<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $services */

$categoryLabels = ['grooming' => 'Grooming', 'boarding' => 'Boarding', 'vet' => 'Vet care', 'training' => 'Training'];
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center max-w-2xl mx-auto" data-reveal>
    <p class="glass inline-flex items-center gap-2 rounded-full text-[#5b21b6] text-xs font-semibold uppercase tracking-widest px-4 py-2">
      <?= icon('calendar', 'h-3.5 w-3.5') ?> Book in under a minute
    </p>
    <h1 class="font-display mt-5 text-4xl font-bold">Our <span class="text-gradient">smart services</span> for your pet</h1>
    <p class="mt-3 text-ink/65">Book a real appointment slot with our team — grooming, boarding, vet visits, and training.</p>
  </div>

  <?php if ($services === []): ?>
    <p class="mt-8 text-center text-ink/60">No services are available to book right now.</p>
  <?php else: ?>
    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($services as $i => $service): ?>
        <a href="/services/<?= e($service['slug']) ?>" class="card-tag card-tag--pop p-6 group flex flex-col" data-reveal style="transition-delay:<?= ($i % 6) * 70 ?>ms">
          <div class="card-tag__tab"><?= e($categoryLabels[$service['category']] ?? $service['category']) ?></div>
          <span class="icon-chip icon-chip-info"><?= icon('calendar', 'h-4 w-4') ?></span>
          <p class="font-display text-lg font-semibold mt-3 group-hover:text-[#7c3aed] transition-colors duration-150"><?= e($service['name']) ?></p>
          <p class="text-sm mt-2 text-ink/60"><?= e($service['description']) ?></p>
          <div class="mt-auto pt-4 flex items-center justify-between text-sm">
            <span class="text-ink/50"><?= (int) $service['duration_minutes'] ?> min</span>
            <span class="font-display font-bold text-gradient"><?= money((int) $service['price_paise']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="mt-12 text-center" data-reveal>
      <a href="/services" class="btn btn-primary">Consult now <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
