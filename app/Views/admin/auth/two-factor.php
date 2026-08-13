<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verification code — <?= e(config('app.name')) ?></title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/build/tailwind.css">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-ink text-paper font-sans antialiased min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-sm">
  <p class="font-display text-2xl font-bold text-center">Happy Tails <span class="text-tennis">Admin</span></p>

  <?php foreach (['error'] as $flashType): ?>
    <?php foreach (\App\Core\Session::getFlash($flashType) as $message): ?>
      <div class="mt-6 border-2 border-leash bg-leash/10 px-4 py-3 text-sm font-medium" role="alert"><?= e($message) ?></div>
    <?php endforeach; ?>
  <?php endforeach; ?>

  <form method="POST" action="/admin/login/2fa" class="mt-8 space-y-5 bg-paper text-ink p-6 border-2 border-paper">
    <?= csrf_field() ?>
    <div>
      <p class="text-sm font-semibold">Enter the 6-digit code</p>
      <p class="mt-1 text-xs text-ink/50">From your authenticator app.</p>
      <label for="code" class="sr-only">Verification code</label>
      <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
             autocomplete="one-time-code" autofocus
             class="input mt-3 text-center text-lg tracking-[0.5em]" placeholder="000000">
    </div>
    <button type="submit" class="w-full btn btn-primary">Verify</button>
  </form>
  <p class="mt-6 text-center text-xs text-paper/50">
    <a href="/admin/login" class="underline hover:no-underline">Back to sign in</a>
  </p>
</div>

</body>
</html>
