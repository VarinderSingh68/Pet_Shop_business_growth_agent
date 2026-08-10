<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Access denied — Happy Tails Pet Store</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{ink:'#12141c',paper:'#f6f7f2',leash:'#e8492a',tennis:'#f2b705'}}}};</script>
</head>
<body class="bg-paper text-ink font-sans antialiased min-h-screen flex items-center justify-center px-4">
  <div class="text-center max-w-md">
    <p class="text-8xl font-bold text-leash">403</p>
    <h1 class="mt-4 text-2xl font-bold">You don't have access to this page.</h1>
    <p class="mt-2 text-ink/60"><?= e($message !== '' ? $message : 'If you think this is a mistake, contact your store administrator.') ?></p>
    <a href="/" class="mt-8 inline-block btn btn-primary">Back to the shop</a>
  </div>
</body>
</html>
