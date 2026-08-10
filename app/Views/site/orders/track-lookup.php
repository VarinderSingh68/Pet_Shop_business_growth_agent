<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var string $orderNumber */
/** @var string|null $error */
?>

<section class="mx-auto max-w-md px-4 sm:px-6 py-20">
  <h1 class="font-display text-2xl font-bold">Track order <?= e($orderNumber) ?></h1>
  <p class="mt-2 text-ink/60">Enter the email you used at checkout to view this order.</p>

  <?php if ($error): ?>
    <div class="mt-6 border-2 border-leash bg-leash/10 px-4 py-3 text-sm font-medium"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="GET" action="/orders/track/<?= e($orderNumber) ?>" class="mt-6 flex gap-2">
    <label for="email" class="sr-only">Email</label>
    <input id="email" type="email" name="email" required class="flex-1 input">
    <button type="submit" class="btn btn-primary">View</button>
  </form>
</section>

<?php \App\Core\View::stop(); ?>
