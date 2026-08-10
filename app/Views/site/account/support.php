<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $tickets */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'support']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">Support</h1>
      <p class="mt-2 text-ink/60">Question about an order or a product? Send us a message and we'll reply by email.</p>

      <form method="POST" action="/account/support" class="mt-6 card-tag p-6 space-y-4 max-w-xl">
        <?= csrf_field() ?>
        <div>
          <label for="subject" class="block text-sm font-semibold mb-1">Subject</label>
          <input id="subject" type="text" name="subject" required class="input">
        </div>
        <div>
          <label for="order_number" class="block text-sm font-semibold mb-1">Order number <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="order_number" type="text" name="order_number" placeholder="HT-2026-XXXXXX" class="input">
        </div>
        <div>
          <label for="message" class="block text-sm font-semibold mb-1">Message</label>
          <textarea id="message" name="message" rows="5" required class="input"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send message</button>
      </form>

      <?php if ($tickets !== []): ?>
        <div class="mt-10">
          <h2 class="font-display text-lg font-semibold">Previous messages</h2>
          <div class="mt-3 space-y-3">
            <?php foreach ($tickets as $t): ?>
              <div class="card-tag p-4">
                <div class="flex justify-between items-start">
                  <p class="font-semibold"><?= e($t['subject']) ?></p>
                  <span class="badge badge-info capitalize"><?= e(str_replace('_', ' ', $t['status'])) ?></span>
                </div>
                <p class="text-sm text-ink/70 mt-1"><?= e($t['message']) ?></p>
                <?php if ($t['staff_reply']): ?>
                  <div class="mt-3 border-t border-mist pt-3">
                    <p class="text-xs font-semibold text-fern uppercase tracking-wide">Reply</p>
                    <p class="text-sm mt-1"><?= e($t['staff_reply']) ?></p>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
