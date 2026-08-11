<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var string $storeAddress */
/** @var string $storePhone */
/** @var string $storeEmail */
?>

<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center max-w-xl mx-auto" data-reveal>
    <p class="glass inline-flex items-center gap-2 rounded-full text-[#5b21b6] text-xs font-semibold uppercase tracking-widest px-4 py-2">
      <?= icon('mail', 'h-3.5 w-3.5') ?> We usually reply within a day
    </p>
    <h1 class="font-display mt-5 text-4xl font-bold">Get in <span class="text-gradient">touch</span></h1>
    <p class="mt-3 text-ink/65">Question about an order, a product, or your pet's next visit? Send us a message.</p>
  </div>

  <div class="mt-10 grid lg:grid-cols-2 gap-8 items-start">
    <form method="POST" action="/contact" class="card-tag p-6 sm:p-8 space-y-4" data-reveal>
      <?= csrf_field() ?>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="name" class="block text-sm font-semibold mb-1">Name</label>
          <input id="name" type="text" name="name" required value="<?= e(old('name')) ?>" class="input">
        </div>
        <div>
          <label for="email" class="block text-sm font-semibold mb-1">Email</label>
          <input id="email" type="email" name="email" required value="<?= e(old('email')) ?>" class="input">
        </div>
      </div>
      <div>
        <label for="phone" class="block text-sm font-semibold mb-1">Phone <span class="font-normal text-ink/50">(optional)</span></label>
        <input id="phone" type="tel" name="phone" value="<?= e(old('phone')) ?>" class="input">
      </div>
      <div>
        <label for="subject" class="block text-sm font-semibold mb-1">Subject</label>
        <input id="subject" type="text" name="subject" required value="<?= e(old('subject')) ?>" class="input">
      </div>
      <div>
        <label for="message" class="block text-sm font-semibold mb-1">Message</label>
        <textarea id="message" name="message" rows="5" required class="input"></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-full">Send message</button>
    </form>

    <div data-reveal style="transition-delay:120ms">
      <div class="card-tag aspect-video text-paper flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #4c1d95, #7c3aed 55%, #ec4899);">
        <div class="soft-drift">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor" class="opacity-90" aria-hidden="true">
            <path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
          </svg>
        </div>
        <span class="card-tag__tab">Store location</span>
      </div>
      <div class="card-tag mt-4 p-5 space-y-2.5 text-sm">
        <p class="flex items-start gap-2.5"><span class="icon-chip icon-chip-plum !w-7 !h-7 shrink-0"><?= icon('map-pin', 'h-3.5 w-3.5') ?></span> <span class="pt-1"><?= e($storeAddress) ?></span></p>
        <p class="flex items-center gap-2.5"><span class="icon-chip icon-chip-info !w-7 !h-7 shrink-0"><?= icon('life-buoy', 'h-3.5 w-3.5') ?></span> <?= e($storePhone) ?></p>
        <p class="flex items-center gap-2.5"><span class="icon-chip icon-chip-fern !w-7 !h-7 shrink-0"><?= icon('mail', 'h-3.5 w-3.5') ?></span> <?= e($storeEmail) ?></p>
        <a href="https://maps.google.com/?q=<?= urlencode($storeAddress) ?>" target="_blank" rel="noopener"
           class="inline-block mt-1 pl-9 text-sm font-semibold text-[#7c3aed] hover:underline">Get directions &rarr;</a>
      </div>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
