<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $appointments */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'appointments']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">My appointments</h1>

      <?php if ($appointments === []): ?>
        <div class="mt-8 card-tag p-10 text-center">
          <p class="font-semibold">No appointments yet.</p>
          <a href="/services" class="mt-4 inline-block btn btn-primary">Book a service</a>
        </div>
      <?php else: ?>
        <div class="mt-6 space-y-3">
          <?php foreach ($appointments as $apt): ?>
            <div class="card-tag p-5 flex items-center justify-between flex-wrap gap-3">
              <div>
                <p class="font-semibold"><?= e($apt['service_name']) ?> <span class="text-ink/50 font-normal">with <?= e($apt['staff_name']) ?></span></p>
                <p class="text-sm text-ink/60 mt-1"><?= date('l, d M Y \a\t g:i A', strtotime($apt['start_at'])) ?></p>
              </div>
              <div class="flex items-center gap-3">
                <span class="badge badge-info capitalize"><?= e($apt['status']) ?></span>
                <?php if (in_array($apt['status'], ['booked', 'confirmed'], true)): ?>
                  <form method="POST" action="/account/appointments/<?= (int) $apt['id'] ?>/cancel">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs font-semibold text-leash hover:underline">Cancel</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
