<?php \App\Core\View::extend('layouts/site'); \App\Core\View::start('content'); ?>

<div class="relative" data-leash-line>
  <svg class="pointer-events-none absolute inset-0 h-full w-full -z-0 hidden md:block" viewBox="0 0 40 1000" preserveAspectRatio="none" aria-hidden="true">
    <path class="leash-line" d="M20,0 C34,60 6,140 20,220 C34,300 6,380 20,460 C34,540 6,620 20,700 C34,780 6,860 20,940 C28,970 16,990 20,1000" />
    <g class="paw-waypoint" data-at="0.02" transform="translate(20,10)">
      <circle r="9"></circle>
      <path transform="translate(-4,-4) scale(0.35)" d="M12 2c-1.5 0-3 1.7-3 3.7S10.5 9 12 9s3-1.6 3-3.3S13.5 2 12 2zM6 6c-1.4 0-2.6 1.6-2.6 3.4S4.6 12.5 6 12.5s2.6-1.4 2.6-3.1S7.4 6 6 6zM18 6c-1.4 0-2.6 1.6-2.6 3.4s1.2 3.1 2.6 3.1 2.6-1.4 2.6-3.1S19.4 6 18 6zM12 10c-3 0-7 3.8-7 7.5 0 2 1.6 3.5 3.6 3.5 1.3 0 2-.5 3.4-.5s2.1.5 3.4.5c2 0 3.6-1.5 3.6-3.5C19 13.8 15 10 12 10z"/>
    </g>
    <g class="paw-waypoint" data-at="0.28" transform="translate(20,280)">
      <circle r="9"></circle>
      <path transform="translate(-4,-4) scale(0.35)" d="M12 2c-1.5 0-3 1.7-3 3.7S10.5 9 12 9s3-1.6 3-3.3S13.5 2 12 2zM6 6c-1.4 0-2.6 1.6-2.6 3.4S4.6 12.5 6 12.5s2.6-1.4 2.6-3.1S7.4 6 6 6zM18 6c-1.4 0-2.6 1.6-2.6 3.4s1.2 3.1 2.6 3.1 2.6-1.4 2.6-3.1S19.4 6 18 6zM12 10c-3 0-7 3.8-7 7.5 0 2 1.6 3.5 3.6 3.5 1.3 0 2-.5 3.4-.5s2.1.5 3.4.5c2 0 3.6-1.5 3.6-3.5C19 13.8 15 10 12 10z"/>
    </g>
    <g class="paw-waypoint" data-at="0.55" transform="translate(20,550)">
      <circle r="9"></circle>
      <path transform="translate(-4,-4) scale(0.35)" d="M12 2c-1.5 0-3 1.7-3 3.7S10.5 9 12 9s3-1.6 3-3.3S13.5 2 12 2zM6 6c-1.4 0-2.6 1.6-2.6 3.4S4.6 12.5 6 12.5s2.6-1.4 2.6-3.1S7.4 6 6 6zM18 6c-1.4 0-2.6 1.6-2.6 3.4s1.2 3.1 2.6 3.1 2.6-1.4 2.6-3.1S19.4 6 18 6zM12 10c-3 0-7 3.8-7 7.5 0 2 1.6 3.5 3.6 3.5 1.3 0 2-.5 3.4-.5s2.1.5 3.4.5c2 0 3.6-1.5 3.6-3.5C19 13.8 15 10 12 10z"/>
    </g>
    <g class="paw-waypoint" data-at="0.82" transform="translate(20,820)">
      <circle r="9"></circle>
      <path transform="translate(-4,-4) scale(0.35)" d="M12 2c-1.5 0-3 1.7-3 3.7S10.5 9 12 9s3-1.6 3-3.3S13.5 2 12 2zM6 6c-1.4 0-2.6 1.6-2.6 3.4S4.6 12.5 6 12.5s2.6-1.4 2.6-3.1S7.4 6 6 6zM18 6c-1.4 0-2.6 1.6-2.6 3.4s1.2 3.1 2.6 3.1 2.6-1.4 2.6-3.1S19.4 6 18 6zM12 10c-3 0-7 3.8-7 7.5 0 2 1.6 3.5 3.6 3.5 1.3 0 2-.5 3.4-.5s2.1.5 3.4.5c2 0 3.6-1.5 3.6-3.5C19 13.8 15 10 12 10z"/>
    </g>
  </svg>

  <!-- Hero -->
  <section class="relative bg-ink text-paper overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28 grid lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-7 rise-in">
        <p class="inline-block border-2 border-tennis text-tennis text-xs font-semibold uppercase tracking-widest px-3 py-1">Bengaluru · same-day delivery in 12 pin codes</p>
        <h1 class="font-display mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.05]">
          Everything your pet needs.<br class="hidden sm:block"> Nothing they don't.
        </h1>
        <p class="mt-6 max-w-xl text-lg text-paper/75">
          Food, litter, and gear picked for your dog, cat, bird, or fish — plus reminders that
          know when the bag's about to run out. Add your pet's profile once; we'll do the remembering.
        </p>
        <div class="mt-8 flex flex-wrap gap-4">
          <a href="/shop" class="btn !border-paper bg-leash text-paper hover:bg-tennis hover:text-ink">Shop the catalogue</a>
          <a href="/account/register" class="btn btn-ghost !border-2 !border-paper/40 !text-paper hover:!border-paper">Create a pet profile</a>
        </div>
      </div>
      <div class="lg:col-span-5 rise-in" style="animation-delay:120ms">
        <div class="card-tag card-tag--pop p-8 bg-paper text-ink">
          <div class="card-tag__tab pop-in" style="animation-delay:600ms">Live</div>
          <div class="flex items-center gap-2.5">
            <span class="icon-chip icon-chip-fern"><?= icon('chart-bar', 'h-4 w-4') ?></span>
            <p class="text-xs uppercase tracking-widest text-fern font-semibold">Growth Copilot, today</p>
          </div>
          <p class="font-display text-2xl font-bold mt-4 leading-snug"><span class="stat-tile__value" data-count-to="23" data-count-duration="1000">0</span> dogs are due for a food reorder this week.</p>
          <p class="mt-3 text-sm text-ink/70">9 owners have never tried a subscription. Estimated revenue if we offer one: <strong>₹<span data-count-to="18400" data-count-duration="1200">0</span></strong>.</p>
          <div class="mt-5 h-px bg-mist"></div>
          <p class="mt-5 text-xs text-ink/50">This is what runs quietly in the admin panel — see §6 of the build.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured categories -->
  <section class="bg-paper py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4">
        <h2 class="font-display text-3xl font-bold">Shop by who you're shopping for</h2>
        <a href="/shop" class="text-sm font-semibold text-leash hover:underline">View all categories &rarr;</a>
      </div>
      <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ([
          ['label' => 'Dogs', 'sub' => 'Food, treats, leads', 'icon' => 'paw', 'chip' => 'icon-chip-leash'],
          ['label' => 'Cats', 'sub' => 'Litter, scratchers, food', 'icon' => 'paw', 'chip' => 'icon-chip-plum'],
          ['label' => 'Birds', 'sub' => 'Seed, cages, toys', 'icon' => 'feather', 'chip' => 'icon-chip-sky'],
          ['label' => 'Fish', 'sub' => 'Tanks, filters, food', 'icon' => 'fish', 'chip' => 'icon-chip-info'],
        ] as $i => $cat): ?>
          <a href="/shop?pet=<?= e(strtolower($cat['label'])) ?>" class="card-tag card-tag--pop rise-in p-6 hover:bg-ink hover:text-paper transition-colors duration-150 group" style="animation-delay:<?= $i * 80 ?>ms">
            <div class="card-tag__tab">Shop</div>
            <span class="icon-chip <?= e($cat['chip']) ?> mb-4"><?= icon($cat['icon'], 'h-5 w-5') ?></span>
            <p class="font-display text-xl font-bold"><?= e($cat['label']) ?></p>
            <p class="text-sm mt-1 text-ink/60 group-hover:text-paper/70"><?= e($cat['sub']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Why pet profiles -->
  <section class="bg-fern text-paper py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <p class="text-xs uppercase tracking-widest text-tennis font-semibold">One profile, less guesswork</p>
        <h2 class="font-display mt-3 text-3xl sm:text-4xl font-bold leading-tight">Tell us about your pet once. We'll handle the rest.</h2>
        <p class="mt-5 text-paper/80 max-w-lg">
          Weight, breed, and birthday drive everything from portion-size suggestions to
          "feeds your dog for ~18 days" labels on every bag, vaccination reminders, and a
          birthday coupon that actually lands on the day.
        </p>
        <a href="/account/register" class="btn mt-8 !border-paper bg-paper text-ink hover:bg-tennis">Add your pet</a>
      </div>
      <div class="grid grid-cols-2 gap-5">
        <?php foreach ([
          ['label' => 'Portion calculator', 'icon' => 'box'],
          ['label' => 'Reorder reminders', 'icon' => 'refresh'],
          ['label' => 'Vaccination alerts', 'icon' => 'shield-check'],
          ['label' => 'Birthday offers', 'icon' => 'gift'],
        ] as $i => $feature): ?>
          <div class="border-2 border-paper/30 p-5 rise-in hover:border-paper/60 transition-colors duration-150" style="animation-delay:<?= $i * 80 ?>ms">
            <span class="icon-chip !bg-paper/10 !text-tennis mb-3"><?= icon($feature['icon'], 'h-5 w-5') ?></span>
            <p class="font-display text-lg font-semibold"><?= e($feature['label']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="bg-paper py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 class="font-display text-3xl font-bold">Pet owners who don't miss a reorder anymore</h2>
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
          <blockquote class="card-tag card-tag--pop rise-in p-6" style="animation-delay:<?= $i * 100 ?>ms">
            <div class="card-tag__tab !bg-tennis flex items-center gap-1"><?= str_repeat('★', (int) $t['rating']) ?></div>
            <span class="icon-chip icon-chip-plum mb-4"><?= icon('heart', 'h-4 w-4') ?></span>
            <p class="text-ink/85">&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
            <footer class="mt-4 text-sm font-semibold"><?= e($t['customer_name']) ?><?php if ($t['pet_description']): ?> <span class="font-normal text-ink/50">&mdash; <?= e($t['pet_description']) ?></span><?php endif; ?></footer>
          </blockquote>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>

<?php \App\Core\View::stop(); ?>
