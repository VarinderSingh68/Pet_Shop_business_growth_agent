<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Too many requests — Happy Tails Pet Store</title>
<link rel="stylesheet" href="/assets/build/tailwind.css">
<link rel="stylesheet" href="/assets/css/app.css">
<style>:root { --leash: #e8492a; --danger: #dc2626; --info: #2563eb; --plum: #7c3aed; --sky: #2f7fb8; }</style>
</head>
<body class="bg-paper text-ink font-sans antialiased min-h-screen flex items-center justify-center px-4 relative overflow-hidden">
  <div class="pointer-events-none absolute -top-24 -left-16 w-72 h-72 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #fbcfe8, transparent 70%);"></div>
  <div class="pointer-events-none absolute -bottom-24 -right-16 w-72 h-72 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #bae6fd, transparent 70%);"></div>

  <div class="card-tag relative text-center max-w-md p-10">
    <div class="card-tag__tab">Rate limited</div>
    <span class="icon-chip icon-chip-leash mx-auto !w-14 !h-14 !rounded-full">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L1 21h22L12 2z"/><line x1="12" y1="9" x2="12" y2="14"/><circle cx="12" cy="17.3" r="0.9" fill="currentColor" stroke="none"/></svg>
    </span>
    <p class="font-display mt-5 text-6xl font-bold text-leash">429</p>
    <h1 class="mt-3 text-2xl font-bold">Slow down a little.</h1>
    <p class="mt-2 text-ink/60">You've made too many requests in a short time. Please wait a moment and try again.</p>
    <a href="/" class="mt-8 inline-flex btn btn-primary">Back to the shop</a>
  </div>
</body>
</html>
