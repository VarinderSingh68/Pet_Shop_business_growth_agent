<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title) ? e($title) . ' — ' : '' ?><?= e(config('app.name')) ?></title>
<meta name="description" content="<?= e($description ?? 'Food, gear, grooming and vet care for the pet you\'re raising — plus reminders that keep up with them.') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ink: '#12141c',
          paper: '#f6f7f2',
          leash: '<?= e(theme_accent_hex()) ?>',
          tennis: '#f2b705',
          fern: '#1f5f4a',
          mist: '#dde3de',
          danger: '#dc2626',
          info: '#2563eb',
          plum: '#7c3aed',
          sky: '#2f7fb8',
        },
        fontFamily: {
          display: ['"Bricolage Grotesque"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
      },
    },
  };
</script>
<link rel="stylesheet" href="/assets/css/app.css">
<style>:root { --leash: <?= e(theme_accent_hex()) ?>; --danger: #dc2626; --info: #2563eb; --plum: #7c3aed; --sky: #2f7fb8; } [x-cloak] { display: none !important; }</style>
<script defer src="/assets/js/alpine.min.js"></script>
<script defer src="/assets/js/leash-line.js"></script>
<script defer src="/assets/js/count-up.js"></script>
</head>
<body class="bg-paper text-ink font-sans antialiased">

<?php foreach (\App\Models\Banner::activeFor($bannerPage ?? 'all', 'top_bar') as $banner): ?>
  <div class="bg-leash text-paper text-sm font-medium text-center py-2 px-4">
    <?= e($banner['title']) ?><?= $banner['body'] ? ' — ' . e($banner['body']) : '' ?>
    <?php if ($banner['link_url']): ?>
      <a href="<?= e($banner['link_url']) ?>" class="ml-2 underline font-semibold"><?= e($banner['link_label'] ?? 'Learn more') ?></a>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if (\App\Core\Session::has('_impersonating_admin_id')): ?>
  <div class="bg-tennis text-ink text-sm font-semibold text-center py-2 px-4 flex items-center justify-center gap-4">
    <span>You're viewing the storefront as <?= e(auth()->user()['name'] ?? 'a customer') ?> (support mode).</span>
    <form method="POST" action="/admin/customers/stop-impersonating">
      <?= csrf_field() ?>
      <button type="submit" class="underline hover:no-underline">Stop</button>
    </form>
  </div>
<?php endif; ?>

<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-leash focus:text-paper focus:px-4 focus:py-2">Skip to content</a>

<?php
  $cartItemCount = 0;
  if (auth()->check()) {
    $cartRow = \App\Core\Database::instance()->selectOne('SELECT id FROM carts WHERE user_id = :uid ORDER BY id DESC LIMIT 1', ['uid' => auth()->id()]);
    if ($cartRow) { $cartItemCount = \App\Models\Cart::itemCount((int) $cartRow['id']); }
  } elseif (!empty($_COOKIE['petshop_cart'])) {
    $cartRow = \App\Core\Database::instance()->selectOne('SELECT id FROM carts WHERE session_token = :t LIMIT 1', ['t' => $_COOKIE['petshop_cart']]);
    if ($cartRow) { $cartItemCount = \App\Models\Cart::itemCount((int) $cartRow['id']); }
  }
?>

<header class="border-b-2 border-ink bg-paper sticky top-0 z-40" x-data="{ open: false }">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between">
      <a href="/" class="flex items-center gap-2 font-display text-xl font-bold tracking-tight">
        <span class="icon-chip icon-chip-leash !w-8 !h-8 !rounded-md"><?= icon('paw', 'h-4 w-4') ?></span>
        Happy&nbsp;Tails
      </a>

      <nav class="hidden md:flex items-center gap-8 text-sm font-medium" aria-label="Primary">
        <a href="/shop" class="relative py-1 hover:text-leash transition-colors duration-150 after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-leash after:transition-all after:duration-200 hover:after:w-full">Shop</a>
        <a href="/services" class="relative py-1 hover:text-leash transition-colors duration-150 after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-leash after:transition-all after:duration-200 hover:after:w-full">Services</a>
        <a href="/blog" class="relative py-1 hover:text-leash transition-colors duration-150 after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-leash after:transition-all after:duration-200 hover:after:w-full">Blog</a>
        <a href="/contact" class="relative py-1 hover:text-leash transition-colors duration-150 after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 after:bg-leash after:transition-all after:duration-200 hover:after:w-full">Contact</a>
      </nav>

      <div class="hidden md:block relative w-64"
           x-data="{ q: '', results: [], open: false, timer: null,
                      search() { clearTimeout(this.timer); this.timer = setTimeout(async () => {
                        if (this.q.length < 2) { this.results = []; return; }
                        const res = await fetch('/api/v1/search/autocomplete?q=' + encodeURIComponent(this.q));
                        const data = await res.json();
                        this.results = data.results; this.open = true;
                      }, 200); } }"
           @click.outside="open = false">
        <label for="site-search" class="sr-only">Search products</label>
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'h-4 w-4') ?></span>
        <input id="site-search" type="search" x-model="q" @input="search()" @focus="open = results.length > 0"
               placeholder="Search food, toys, grooming…" autocomplete="off"
               class="input pl-9">
        <ul x-show="open && results.length" x-cloak
            class="absolute z-50 mt-1 w-full border-2 border-ink bg-paper max-h-80 overflow-y-auto">
          <template x-for="r in results" :key="r.url">
            <li>
              <a :href="r.url" class="flex justify-between px-3 py-2 text-sm hover:bg-tennis/30">
                <span x-text="r.name"></span>
                <span class="text-ink/50" x-text="r.price"></span>
              </a>
            </li>
          </template>
        </ul>
      </div>

      <div class="flex items-center gap-4">
        <a href="/cart" class="relative flex items-center justify-center text-ink hover:text-leash transition-colors duration-150" aria-label="Cart<?= $cartItemCount > 0 ? ' — ' . $cartItemCount . ' item' . ($cartItemCount === 1 ? '' : 's') : '' ?>">
          <?= icon('cart', 'h-6 w-6') ?>
          <?php if ($cartItemCount > 0): ?>
            <span class="pulse-ring absolute -top-1.5 -right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-leash px-1 text-[10px] font-bold text-paper"><?= $cartItemCount > 99 ? '99+' : $cartItemCount ?></span>
          <?php endif; ?>
        </a>
        <?php if (auth()->check()): ?>
          <?php $navUser = auth()->user(); ?>
          <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false" @keydown.escape="userMenuOpen = false">
            <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen" aria-haspopup="true"
                    class="flex items-center gap-2 hover:text-leash transition-colors duration-150">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-ink text-paper text-xs font-bold" aria-hidden="true">
                <?= e(mb_strtoupper(mb_substr($navUser['name'], 0, 1))) ?>
              </span>
              <span class="hidden lg:inline text-sm font-medium max-w-[8rem] truncate"><?= e(explode(' ', (string) $navUser['name'])[0]) ?></span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"
                   class="hidden lg:block transition-transform duration-150" :class="userMenuOpen ? 'rotate-180' : ''">
                <path d="M6 9l6 6 6-6"/>
              </svg>
            </button>

            <div x-show="userMenuOpen" x-cloak x-transition.origin.top.right
                 class="absolute right-0 mt-2 w-64 border-2 border-ink bg-paper shadow-lg z-50" role="menu">
              <div class="p-4 border-b-2 border-mist">
                <p class="font-semibold text-sm truncate"><?= e($navUser['name']) ?></p>
                <p class="text-xs text-ink/50 truncate"><?= e($navUser['email']) ?></p>
              </div>
              <nav class="py-1 text-sm" role="none">
                <a href="/account" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('users', 'h-4 w-4') ?></span> My account</a>
                <a href="/account/orders" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('cart', 'h-4 w-4') ?></span> Orders</a>
                <a href="/account/pets" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('paw', 'h-4 w-4') ?></span> My pets</a>
                <a href="/account/appointments" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('calendar', 'h-4 w-4') ?></span> Appointments</a>
                <a href="/account/subscriptions" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('refresh', 'h-4 w-4') ?></span> Subscriptions</a>
                <a href="/account/rewards" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('gift', 'h-4 w-4') ?></span> Rewards</a>
                <a href="/account/wishlist" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('heart', 'h-4 w-4') ?></span> Wishlist</a>
                <a href="/account/addresses" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('map-pin', 'h-4 w-4') ?></span> Addresses</a>
                <a href="/account/support" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50"><span class="text-ink/50"><?= icon('life-buoy', 'h-4 w-4') ?></span> Support</a>
                <?php if (\App\Core\App::auth()->hasRole('owner', 'manager', 'staff', 'developer')): ?>
                  <a href="/admin" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-mist/50 border-t-2 border-mist mt-1 pt-2 font-semibold text-leash"><?= icon('shield-check', 'h-4 w-4') ?> Admin panel</a>
                <?php endif; ?>
              </nav>
              <form method="POST" action="/account/logout" class="border-t-2 border-mist p-2">
                <?= csrf_field() ?>
                <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 text-left px-2 py-2 text-sm font-semibold hover:bg-mist/50"><?= icon('arrow-right', 'h-4 w-4') ?> Sign out</button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <a href="/account/login" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
        <button class="md:hidden" @click="open = !open" aria-label="Toggle menu" :aria-expanded="open">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>

    <nav x-show="open" x-cloak class="md:hidden pb-4 flex flex-col gap-1 text-sm font-medium" aria-label="Mobile">
      <a href="/shop" class="flex items-center gap-2.5 rounded px-2 py-2 hover:bg-mist/50"><span class="text-leash"><?= icon('box', 'h-4 w-4') ?></span> Shop</a>
      <a href="/services" class="flex items-center gap-2.5 rounded px-2 py-2 hover:bg-mist/50"><span class="text-leash"><?= icon('calendar', 'h-4 w-4') ?></span> Services</a>
      <a href="/blog" class="flex items-center gap-2.5 rounded px-2 py-2 hover:bg-mist/50"><span class="text-leash"><?= icon('document', 'h-4 w-4') ?></span> Blog</a>
      <a href="/contact" class="flex items-center gap-2.5 rounded px-2 py-2 hover:bg-mist/50"><span class="text-leash"><?= icon('mail', 'h-4 w-4') ?></span> Contact</a>
    </nav>
  </div>
</header>

<?php foreach (['success', 'error'] as $flashType): ?>
  <?php foreach (\App\Core\Session::getFlash($flashType) as $message): ?>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4" role="status">
      <div class="border-2 <?= $flashType === 'success' ? 'border-fern bg-fern/10' : 'border-leash bg-leash/10' ?> px-4 py-3 text-sm font-medium">
        <?= e($message) ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endforeach; ?>

<main id="main">
<?= \App\Core\View::section('content') ?>
</main>

<footer class="mt-24 border-t-2 border-ink bg-ink text-paper">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div>
      <p class="flex items-center gap-2 font-display text-lg font-bold">
        <span class="icon-chip !bg-tennis !text-ink !w-8 !h-8 !rounded-md"><?= icon('paw', 'h-4 w-4') ?></span>
        Happy Tails
      </p>
      <p class="mt-3 text-sm text-paper/70">Food, gear, and care for the animal you're raising — Bengaluru, India.</p>
    </div>
    <div>
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-tennis"><?= icon('box', 'h-4 w-4') ?> Shop</p>
      <ul class="mt-3 space-y-2 text-sm text-paper/80">
        <li><a href="/shop?pet=dog" class="hover:text-paper">Dogs</a></li>
        <li><a href="/shop?pet=cat" class="hover:text-paper">Cats</a></li>
        <li><a href="/shop?pet=bird" class="hover:text-paper">Birds</a></li>
        <li><a href="/shop?pet=fish" class="hover:text-paper">Fish</a></li>
      </ul>
    </div>
    <div>
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-tennis"><?= icon('document', 'h-4 w-4') ?> Store</p>
      <ul class="mt-3 space-y-2 text-sm text-paper/80">
        <li><a href="/services" class="hover:text-paper">Services</a></li>
        <li><a href="/blog" class="hover:text-paper">Blog</a></li>
        <li><a href="/faq" class="hover:text-paper">FAQ</a></li>
        <li><a href="/contact" class="hover:text-paper">Contact</a></li>
        <li><a href="/legal/privacy" class="hover:text-paper">Privacy</a></li>
      </ul>
    </div>
    <div class="rounded-lg bg-paper/5 p-5 border border-paper/10">
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-tennis"><?= icon('mail', 'h-4 w-4') ?> Newsletter</p>
      <p class="mt-3 text-sm text-paper/70">Reminders, offers, and the occasional good boy photo.</p>
      <form method="POST" action="/newsletter/subscribe" class="mt-3 flex gap-2">
        <?= csrf_field() ?>
        <label for="newsletter-email" class="sr-only">Email</label>
        <input id="newsletter-email" type="email" name="email" required placeholder="you@example.com"
               class="min-w-0 flex-1 border-2 border-paper/40 bg-transparent px-3 py-2 text-sm placeholder:text-paper/40 focus:border-paper outline-none">
        <button type="submit" class="btn btn-sm !border-tennis bg-tennis text-ink hover:bg-paper">Join</button>
      </form>
    </div>
  </div>
  <div class="border-t border-paper/20 py-4 text-center text-xs text-paper/60">
    &copy; <?= date('Y') ?> Happy Tails Pet Store. All rights reserved.
  </div>
</footer>

<a href="https://wa.me/910000000000" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
   class="pulse-ring fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-fern text-paper shadow-lg hover:scale-110 transition-transform duration-150">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 6.3A8.9 8.9 0 0 0 12 4a9 9 0 0 0-7.9 13.4L3 21l3.7-1a9 9 0 0 0 5.3 1.7 9 9 0 0 0 9-9 8.9 8.9 0 0 0-2.4-6.4Zm-5.6 13.8a7.5 7.5 0 0 1-3.8-1l-.3-.2-2.2.6.6-2.2-.2-.3a7.5 7.5 0 1 1 6 3.1Zm4.1-5.6c-.2-.1-1.3-.7-1.5-.7-.2-.1-.3-.1-.5.1s-.6.7-.8.9-.3.2-.6 0a6.1 6.1 0 0 1-1.8-1.1 6.7 6.7 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.5l.4-.4.2-.4a.5.5 0 0 0 0-.4c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.6 10.6 0 0 0 4.1 3.7 4.7 4.7 0 0 0 2.9.6 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c0-.2-.2-.2-.4-.4Z"/></svg>
</a>

<div x-data="{ visible: !localStorage.getItem('petshop_cookie_consent') }" x-show="visible" x-cloak x-transition.origin.bottom
     class="fixed inset-x-0 bottom-0 z-50 bg-ink text-paper border-t-2 border-paper/20 px-4 py-4 sm:px-6">
  <div class="mx-auto max-w-5xl flex flex-col sm:flex-row items-center justify-between gap-3">
    <p class="flex items-center gap-3 text-sm text-paper/80">
      <span class="icon-chip !bg-tennis/20 !text-tennis shrink-0"><?= icon('info', 'h-4 w-4') ?></span>
      We use cookies to keep your cart working and to understand how the store is used. See our <a href="/legal/privacy" class="underline">privacy policy</a>.
    </p>
    <button type="button" @click="localStorage.setItem('petshop_cookie_consent','1'); visible = false"
            class="btn btn-sm shrink-0 !border-tennis bg-tennis text-ink hover:bg-paper">Got it</button>
  </div>
</div>

</body>
</html>
