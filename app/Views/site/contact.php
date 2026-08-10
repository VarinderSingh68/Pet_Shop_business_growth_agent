<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var string $storeAddress */
/** @var string $storePhone */
/** @var string $storeEmail */
?>

<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-14">
  <h1 class="font-display text-3xl font-bold">Contact us</h1>
  <p class="mt-2 text-ink/60 max-w-xl">Question about an order, a product, or your pet's next visit? Send us a message.</p>

  <div class="mt-8 grid lg:grid-cols-2 gap-10">
    <form method="POST" action="/contact" class="space-y-4">
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
      <button type="submit" class="btn btn-primary">Send message</button>
    </form>

    <div>
      <div class="card-tag aspect-video bg-fern text-paper flex items-center justify-center relative overflow-hidden">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor" class="opacity-90" aria-hidden="true">
          <path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
        </svg>
        <span class="absolute bottom-3 right-3 text-xs font-semibold uppercase tracking-wide opacity-70">Store location</span>
      </div>
      <div class="mt-4 space-y-2 text-sm">
        <p><strong>Address:</strong> <?= e($storeAddress) ?></p>
        <p><strong>Phone:</strong> <?= e($storePhone) ?></p>
        <p><strong>Email:</strong> <?= e($storeEmail) ?></p>
        <a href="https://maps.google.com/?q=<?= urlencode($storeAddress) ?>" target="_blank" rel="noopener"
           class="inline-block mt-2 text-sm font-semibold text-leash hover:underline">Get directions &rarr;</a>
      </div>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
