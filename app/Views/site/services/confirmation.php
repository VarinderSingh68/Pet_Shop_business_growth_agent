<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $appointment */
?>

<section class="mx-auto max-w-2xl px-4 sm:px-6 py-16 text-center">
  <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-fern text-paper">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
  </div>
  <h1 class="font-display text-3xl font-bold mt-6">Booking confirmed</h1>
  <p class="mt-2 text-ink/60">Confirmation number <strong><?= e($appointment['booking_number']) ?></strong></p>

  <div class="mt-8 card-tag p-6 text-left">
    <div class="card-tag__tab">Appointment</div>
    <p class="font-display text-lg font-semibold mt-6"><?= e($appointment['service_name']) ?></p>
    <p class="text-sm text-ink/70 mt-1">with <?= e($appointment['staff_name']) ?></p>
    <p class="text-sm text-ink/70 mt-1"><?= date('l, d M Y \a\t g:i A', strtotime($appointment['start_at'])) ?></p>
    <?php if ($appointment['deposit_paise']): ?>
      <p class="text-sm mt-3 text-tennis font-semibold">Deposit of <?= money((int) $appointment['deposit_paise']) ?> due at the store.</p>
    <?php endif; ?>
  </div>

  <a href="/" class="mt-8 inline-block btn btn-primary">Back to home</a>
</section>

<?php \App\Core\View::stop(); ?>
