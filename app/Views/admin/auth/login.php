<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin sign in — <?= e(config('app.name')) ?></title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: {
    colors: { ink: '#12141c', paper: '#f6f7f2', leash: '#e8492a', tennis: '#f2b705', fern: '#1f5f4a', mist: '#dde3de' },
    fontFamily: {
      display: ['"Bricolage Grotesque"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
    },
  } } };
</script>
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

  <form method="POST" action="/admin/login" class="mt-8 space-y-5 bg-paper text-ink p-6 border-2 border-paper">
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
  <p class="mt-6 text-center text-xs text-paper/50">Staff and developer access only.</p>
</div>

</body>
</html>
