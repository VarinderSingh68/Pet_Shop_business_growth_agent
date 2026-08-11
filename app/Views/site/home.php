<?php \App\Core\View::extend('layouts/site'); \App\Core\View::start('content'); ?>

<div class="relative">

  <!-- Hero -->
  <section class="relative overflow-hidden pt-6 pb-20 lg:pt-10 lg:pb-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-7" data-reveal>
        <p class="glass inline-flex items-center gap-2 rounded-full text-[#5b21b6] text-xs font-semibold uppercase tracking-widest px-4 py-2">
          <span class="icon-chip icon-chip-plum !w-5 !h-5 !rounded-full"><?= icon('map-pin', 'h-3 w-3') ?></span>
          Bengaluru · same-day delivery in 12 pin codes
        </p>
        <h1 class="font-display mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.05]">
          Where every pet<br class="hidden sm:block">
          gets <span class="text-gradient">premium care</span>.
        </h1>
        <p class="mt-6 max-w-xl text-lg text-ink/70">
          Food, litter, and gear picked for your dog, cat, bird, or fish — plus reminders that
          know when the bag's about to run out. Add your pet's profile once; we'll do the remembering.
        </p>
        <div class="mt-8 flex flex-wrap gap-4">
          <a href="/shop" class="btn btn-primary">Shop the catalogue</a>
          <a href="/account/register" class="btn btn-secondary">Create a pet profile</a>
        </div>

        <!-- Quick-need pills -->
        <div class="mt-10" data-reveal style="transition-delay:120ms">
          <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-ink/45">
            <?= icon('heart', 'h-3.5 w-3.5') ?> Your pet might need&hellip;
          </p>
          <div class="mt-3 flex flex-wrap gap-2.5">
            <?php foreach ([
              ['label' => 'Routine checkup', 'href' => '/services'],
              ['label' => 'Dental care', 'href' => '/services'],
              ['label' => 'Grooming spa', 'href' => '/services'],
              ['label' => 'Food reorder', 'href' => '/shop'],
            ] as $need): ?>
              <a href="<?= e($need['href']) ?>" class="glass rounded-full px-4 py-2 text-sm font-medium hover:bg-white/80 hover:-translate-y-0.5 transition-all duration-200">
                <?= e($need['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="lg:col-span-5 relative" data-reveal style="transition-delay:160ms">
        <div class="pointer-events-none absolute -top-10 -right-10 w-40 h-40 rounded-full opacity-40 blur-2xl" style="background: radial-gradient(circle, var(--peach), transparent 70%);"></div>
        <div class="card-tag card-tag--pop soft-drift p-8" style="--drift-rot: 1deg;">
          <div class="card-tag__tab pop-in" style="animation-delay:600ms">Live</div>
          <div class="flex items-center gap-2.5">
            <span class="icon-chip icon-chip-fern"><?= icon('chart-bar', 'h-4 w-4') ?></span>
            <p class="text-xs uppercase tracking-widest text-[#0b3d2e] font-semibold">Growth Copilot, today</p>
          </div>
          <p class="font-display text-2xl font-bold mt-4 leading-snug"><span class="text-gradient stat-tile__value" data-count-to="23" data-count-duration="1000">0</span> dogs are due for a food reorder this week.</p>
          <p class="mt-3 text-sm text-ink/70">9 owners have never tried a subscription. Estimated revenue if we offer one: <strong>₹<span data-count-to="18400" data-count-duration="1200">0</span></strong>.</p>
          <div class="mt-5 h-px bg-white/60"></div>
          <p class="mt-5 text-xs text-ink/45">This is what runs quietly in the admin panel — see §6 of the build.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured categories -->
  <section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4" data-reveal>
        <h2 class="font-display text-3xl font-bold">Shop by who you're shopping for</h2>
        <a href="/shop" class="text-sm font-semibold text-[#7c3aed] hover:underline">View all categories &rarr;</a>
      </div>
      <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ([
          ['label' => 'Dogs', 'sub' => 'Food, treats, leads', 'icon' => 'paw', 'chip' => 'icon-chip-leash', 'grad' => 'linear-gradient(135deg, #fbcfe8, #fde68a)'],
          ['label' => 'Cats', 'sub' => 'Litter, scratchers, food', 'icon' => 'paw', 'chip' => 'icon-chip-plum', 'grad' => 'linear-gradient(135deg, #ddd6fe, #fbcfe8)'],
          ['label' => 'Birds', 'sub' => 'Seed, cages, toys', 'icon' => 'feather', 'chip' => 'icon-chip-sky', 'grad' => 'linear-gradient(135deg, #bae6fd, #ddd6fe)'],
          ['label' => 'Fish', 'sub' => 'Tanks, filters, food', 'icon' => 'fish', 'chip' => 'icon-chip-info', 'grad' => 'linear-gradient(135deg, #a7f3d0, #bae6fd)'],
        ] as $i => $cat): ?>
          <a href="/shop?pet=<?= e(strtolower($cat['label'])) ?>" class="card-tag card-tag--pop group flex flex-col" data-reveal style="transition-delay:<?= $i * 80 ?>ms">
            <div class="card-tag__tab">Shop</div>
            <div class="h-24 flex items-center justify-center" style="background: <?= $cat['grad'] ?>;">
              <span class="icon-chip !bg-white/70 !text-ink/80 !shadow-none"><?= icon($cat['icon'], 'h-6 w-6') ?></span>
            </div>
            <div class="p-5">
              <p class="font-display text-xl font-bold"><?= e($cat['label']) ?></p>
              <p class="text-sm mt-1 text-ink/55"><?= e($cat['sub']) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Why pet profiles -->
  <section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="card-tag p-8 sm:p-12 grid lg:grid-cols-2 gap-12 items-center" style="background: linear-gradient(135deg, rgba(167,139,250,0.16), rgba(249,168,212,0.16));" data-reveal>
        <div>
          <p class="text-xs uppercase tracking-widest text-[#7c3aed] font-semibold">One profile, less guesswork</p>
          <h2 class="font-display mt-3 text-3xl sm:text-4xl font-bold leading-tight">Tell us about your pet once. We'll handle the rest.</h2>
          <p class="mt-5 text-ink/70 max-w-lg">
            Weight, breed, and birthday drive everything from portion-size suggestions to
            "feeds your dog for ~18 days" labels on every bag, vaccination reminders, and a
            birthday coupon that actually lands on the day.
          </p>
          <a href="/account/register" class="btn btn-primary mt-8">Add your pet</a>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <?php foreach ([
            ['label' => 'Portion calculator', 'icon' => 'box'],
            ['label' => 'Reorder reminders', 'icon' => 'refresh'],
            ['label' => 'Vaccination alerts', 'icon' => 'shield-check'],
            ['label' => 'Birthday offers', 'icon' => 'gift'],
          ] as $i => $feature): ?>
            <div class="glass rounded-2xl p-5 hover:-translate-y-1 transition-transform duration-200" data-reveal style="transition-delay:<?= $i * 80 ?>ms">
              <span class="icon-chip icon-chip-plum mb-3"><?= icon($feature['icon'], 'h-5 w-5') ?></span>
              <p class="font-display text-base font-semibold"><?= e($feature['label']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 class="font-display text-3xl font-bold" data-reveal>Pet owners who don't miss a reorder anymore</h2>
      <?php
      $fallbackTestimonials = [
          ['quote' => 'The reminder texted me two days before Milo\'s food ran out. I didn\'t have to think about it.', 'customer_name' => 'Priya R.', 'pet_description' => 'Milo, Labrador', 'rating' => 5],
          ['quote' => 'Booked a grooming slot in under a minute and got a reschedule link when I needed to move it.', 'customer_name' => 'Arjun K.', 'pet_description' => 'Simba, Persian cat', 'rating' => 5],
          ['quote' => 'The subscription pauses itself when I travel. No calls, no forgetting to cancel.', 'customer_name' => 'Fatima S.', 'pet_description' => 'Coco, Budgie', 'rating' => 5],
      ];
      $displayTestimonials = $testimonials !== [] ? $testimonials : $fallbackTestimonials;
      ?>
      <div class="mt-10 grid md:grid-cols-3 gap-6">
        <?php foreach ($displayTestimonials as $i => $t): ?>
          <blockquote class="card-tag card-tag--pop p-6" data-reveal style="transition-delay:<?= $i * 100 ?>ms">
            <div class="card-tag__tab flex items-center gap-1"><?= str_repeat('★', (int) $t['rating']) ?></div>
            <span class="icon-chip icon-chip-plum mb-4"><?= icon('heart', 'h-4 w-4') ?></span>
            <p class="text-ink/80">&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
            <footer class="mt-4 text-sm font-semibold"><?= e($t['customer_name']) ?><?php if ($t['pet_description']): ?> <span class="font-normal text-ink/50">&mdash; <?= e($t['pet_description']) ?></span><?php endif; ?></footer>
          </blockquote>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</div>

<?php \App\Core\View::stop(); ?>
