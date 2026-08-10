<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var bool $googleEnabled */
?>

<section class="mx-auto max-w-md px-4 sm:px-6 py-16 sm:py-24">
  <div class="card-tag p-8 sm:p-10">
    <div class="card-tag__tab">Welcome back</div>

    <h1 class="font-display text-3xl font-bold">Sign in</h1>
    <p class="mt-2 text-ink/60">New here? <a href="/account/register" class="text-leash font-semibold hover:underline">Create an account</a>.</p>

    <?php if ($googleEnabled): ?>
      <a href="/account/login/google"
         class="mt-8 flex items-center justify-center gap-3 btn btn-secondary">
        <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
          <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z"/>
          <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l5.7-5.7C34.5 6 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
          <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.4 26.7 36 24 36c-5.3 0-9.7-3.1-11.3-7.8l-6.5 5C9.6 39.6 16.3 44 24 44z"/>
          <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2C40.5 36.3 44 30.7 44 24c0-1.3-.1-2.7-.4-3.5z"/>
        </svg>
        Continue with Google
      </a>

      <div class="mt-6 flex items-center gap-3 text-xs text-ink/40">
        <div class="h-px flex-1 bg-mist"></div>
        <span>or sign in with email</span>
        <div class="h-px flex-1 bg-mist"></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="/account/login" class="<?= $googleEnabled ? 'mt-6' : 'mt-8' ?> space-y-5">
      <?= csrf_field() ?>
      <div>
        <label for="email" class="block text-sm font-semibold mb-1">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>"
               class="input">
      </div>
      <div>
        <label for="password" class="block text-sm font-semibold mb-1">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
               class="input">
      </div>
      <button type="submit" class="w-full btn btn-primary">Sign in</button>
    </form>
  </div>

  <p class="mt-6 text-center text-xs text-ink/40">
    By continuing you agree to our <a href="/legal/terms" class="underline hover:text-ink">Terms</a> and <a href="/legal/privacy" class="underline hover:text-ink">Privacy Policy</a>.
  </p>
</section>

<?php \App\Core\View::stop(); ?>
