<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');
?>

<section class="mx-auto max-w-md px-4 sm:px-6 py-24 text-center">
  <h1 class="font-display text-2xl font-bold">You're unsubscribed</h1>
  <p class="mt-2 text-ink/60">You won't get any more newsletter emails from us. You can resubscribe anytime from the homepage.</p>
  <a href="/" class="mt-8 inline-block btn btn-primary">Back to home</a>
</section>

<?php \App\Core\View::stop(); ?>
