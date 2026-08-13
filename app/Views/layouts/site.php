<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title) ? e($title) . ' — ' : '' ?><?= e(config('app.name')) ?></title>
<meta name="description" content="<?= e($description ?? 'Food, gear, grooming and vet care for the pet you\'re raising — plus reminders that keep up with them.') ?>">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/build/tailwind.css">
<link rel="stylesheet" href="/assets/css/app.css">
<style>:root { --leash: <?= e(theme_accent_hex()) ?>; --danger: #dc2626; --info: #2563eb; --plum: #7c3aed; --sky: #2f7fb8; } [x-cloak] { display: none !important; }</style>
<script defer src="/assets/js/alpine.min.js"></script>
<script defer src="/assets/js/leash-line.js"></script>
<script defer src="/assets/js/count-up.js"></script>
<script defer src="/assets/js/scroll-reveal.js"></script>
</head>
<body class="site-shell text-ink font-sans antialiased">

<div class="site-blob-layer" aria-hidden="true">
  <div class="site-blob site-blob--1"></div>
  <div class="site-blob site-blob--2"></div>
  <div class="site-blob site-blob--3"></div>
  <div class="site-blob site-blob--4"></div>
</div>

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

<header class="sticky top-0 z-40 px-3 sm:px-6 lg:px-8 pt-3" x-data="{ open: false }">
  <div class="mx-auto max-w-7xl">
    <div class="site-nav flex h-16 items-center justify-between rounded-full px-4 sm:px-6 transition-shadow duration-300">
      <a href="/" class="flex items-center gap-2 font-display text-xl font-bold tracking-tight">
        <span class="icon-chip icon-chip-leash !w-9 !h-9 !rounded-full"><?= icon('paw', 'h-4 w-4') ?></span>
        <span class="text-gradient">Happy&nbsp;Tails</span>
      </a>

      <nav class="hidden md:flex items-center gap-1 text-sm font-medium" aria-label="Primary">
        <a href="/shop" class="rounded-full px-4 py-2 transition-colors duration-200 hover:bg-white/60 hover:text-[#7c3aed]">Shop</a>
        <a href="/services" class="rounded-full px-4 py-2 transition-colors duration-200 hover:bg-white/60 hover:text-[#7c3aed]">Services</a>
        <a href="/blog" class="rounded-full px-4 py-2 transition-colors duration-200 hover:bg-white/60 hover:text-[#7c3aed]">Blog</a>
        <a href="/contact" class="rounded-full px-4 py-2 transition-colors duration-200 hover:bg-white/60 hover:text-[#7c3aed]">Contact</a>
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
               class="input !rounded-full pl-9 !bg-white/70 !border-white/80 text-sm">
        <ul x-show="open && results.length" x-cloak
            class="glass absolute z-50 mt-2 w-full rounded-2xl max-h-80 overflow-y-auto">
          <template x-for="r in results" :key="r.url">
            <li>
              <a :href="r.url" class="flex justify-between px-4 py-2.5 text-sm hover:bg-white/70 rounded-xl mx-1 my-1">
                <span x-text="r.name"></span>
                <span class="text-ink/50" x-text="r.price"></span>
              </a>
            </li>
          </template>
        </ul>
      </div>

      <div class="flex items-center gap-4">
        <a href="/cart" class="relative flex items-center justify-center text-ink hover:text-[#7c3aed] transition-colors duration-150"
           x-data="{ count: <?= (int) $cartItemCount ?>, bump: false }"
           x-init="window.addEventListener('cart-updated', (e) => { count = e.detail.count; bump = true; setTimeout(() => bump = false, 300); })"
           :aria-label="'Cart' + (count > 0 ? ' — ' + count + ' item' + (count === 1 ? '' : 's') : '')"
           :class="bump ? 'scale-110' : 'scale-100'" style="transition: transform 150ms ease-out;">
          <?= icon('cart', 'h-6 w-6') ?>
          <span x-show="count > 0" x-cloak x-text="count > 99 ? '99+' : count"
                class="pulse-ring absolute -top-1.5 -right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-leash px-1 text-[10px] font-bold text-paper"></span>
        </a>
        <?php if (auth()->check()): ?>
          <?php $navUser = auth()->user(); ?>
          <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false" @keydown.escape="userMenuOpen = false">
            <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen" aria-haspopup="true"
                    class="flex items-center gap-2 hover:text-[#7c3aed] transition-colors duration-150">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-paper text-xs font-bold" style="background: linear-gradient(135deg, var(--lav), var(--blush));" aria-hidden="true">
                <?= e(mb_strtoupper(mb_substr($navUser['name'], 0, 1))) ?>
              </span>
              <span class="hidden lg:inline text-sm font-medium max-w-[8rem] truncate"><?= e(explode(' ', (string) $navUser['name'])[0]) ?></span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"
                   class="hidden lg:block transition-transform duration-150" :class="userMenuOpen ? 'rotate-180' : ''">
                <path d="M6 9l6 6 6-6"/>
              </svg>
            </button>

            <div x-show="userMenuOpen" x-cloak x-transition.origin.top.right
                 class="glass absolute right-0 mt-3 w-64 rounded-2xl overflow-hidden z-50" role="menu">
              <div class="p-4 border-b border-white/60">
                <p class="font-semibold text-sm truncate"><?= e($navUser['name']) ?></p>
                <p class="text-xs text-ink/50 truncate"><?= e($navUser['email']) ?></p>
              </div>
              <nav class="py-1 text-sm" role="none">
                <a href="/account" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('users', 'h-4 w-4') ?></span> My account</a>
                <a href="/account/orders" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('cart', 'h-4 w-4') ?></span> Orders</a>
                <a href="/account/pets" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('paw', 'h-4 w-4') ?></span> My pets</a>
                <a href="/account/appointments" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('calendar', 'h-4 w-4') ?></span> Appointments</a>
                <a href="/account/subscriptions" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('refresh', 'h-4 w-4') ?></span> Subscriptions</a>
                <a href="/account/rewards" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('gift', 'h-4 w-4') ?></span> Rewards</a>
                <a href="/account/wishlist" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('heart', 'h-4 w-4') ?></span> Wishlist</a>
                <a href="/account/addresses" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('map-pin', 'h-4 w-4') ?></span> Addresses</a>
                <a href="/account/support" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60"><span class="text-ink/50"><?= icon('life-buoy', 'h-4 w-4') ?></span> Support</a>
                <?php if (\App\Core\App::auth()->hasRole('owner', 'manager', 'staff', 'developer')): ?>
                  <a href="/admin" role="menuitem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/60 border-t border-white/60 mt-1 pt-2 font-semibold text-[#7c3aed]"><?= icon('shield-check', 'h-4 w-4') ?> Admin panel</a>
                <?php endif; ?>
              </nav>
              <form method="POST" action="/account/logout" class="border-t border-white/60 p-2">
                <?= csrf_field() ?>
                <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 text-left px-2 py-2 text-sm font-semibold hover:bg-white/60 rounded-xl"><?= icon('arrow-right', 'h-4 w-4') ?> Sign out</button>
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

    <nav x-show="open" x-cloak class="glass md:hidden mt-2 rounded-2xl p-3 flex flex-col gap-1 text-sm font-medium" aria-label="Mobile">
      <a href="/shop" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 hover:bg-white/60"><span class="text-[#7c3aed]"><?= icon('box', 'h-4 w-4') ?></span> Shop</a>
      <a href="/services" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 hover:bg-white/60"><span class="text-[#7c3aed]"><?= icon('calendar', 'h-4 w-4') ?></span> Services</a>
      <a href="/blog" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 hover:bg-white/60"><span class="text-[#7c3aed]"><?= icon('document', 'h-4 w-4') ?></span> Blog</a>
      <a href="/contact" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 hover:bg-white/60"><span class="text-[#7c3aed]"><?= icon('mail', 'h-4 w-4') ?></span> Contact</a>
    </nav>
  </div>
</header>

<?php foreach (['success', 'error'] as $flashType): ?>
  <?php foreach (\App\Core\Session::getFlash($flashType) as $message): ?>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4" role="status">
      <div class="glass rounded-2xl <?= $flashType === 'success' ? 'text-[#0b3d2e]' : 'text-[#7a1a12]' ?> px-5 py-3.5 text-sm font-medium flex items-center gap-2.5">
        <span class="icon-chip <?= $flashType === 'success' ? 'icon-chip-fern' : 'icon-chip-leash' ?> !w-7 !h-7"><?= icon($flashType === 'success' ? 'check' : 'alert-triangle', 'h-3.5 w-3.5') ?></span>
        <?= e($message) ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endforeach; ?>

<main id="main">
<?= \App\Core\View::section('content') ?>
</main>

<footer class="mt-24 mx-3 sm:mx-6 lg:mx-8 mb-3 rounded-[2.5rem] text-paper overflow-hidden relative" style="background: linear-gradient(160deg, #241b3f, #3d1f52 55%, #1c1430);">
  <div class="pointer-events-none absolute -top-24 -right-16 w-72 h-72 rounded-full opacity-25 blur-3xl" style="background: radial-gradient(circle, var(--blush), transparent 70%);"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-16 w-72 h-72 rounded-full opacity-25 blur-3xl" style="background: radial-gradient(circle, var(--skyb), transparent 70%);"></div>
  <div class="relative mx-auto max-w-7xl px-6 sm:px-10 lg:px-12 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div>
      <p class="flex items-center gap-2 font-display text-lg font-bold">
        <span class="icon-chip !w-8 !h-8" style="background: linear-gradient(135deg, var(--lav), var(--blush));"><?= icon('paw', 'h-4 w-4') ?></span>
        Happy Tails
      </p>
      <p class="mt-3 text-sm text-paper/70">Food, gear, and care for the animal you're raising — Bengaluru, India.</p>
    </div>
    <div>
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide" style="color: var(--peach);"><?= icon('box', 'h-4 w-4') ?> Shop</p>
      <ul class="mt-3 space-y-2 text-sm text-paper/80">
        <li><a href="/shop?pet=dog" class="hover:text-paper">Dogs</a></li>
        <li><a href="/shop?pet=cat" class="hover:text-paper">Cats</a></li>
        <li><a href="/shop?pet=bird" class="hover:text-paper">Birds</a></li>
        <li><a href="/shop?pet=fish" class="hover:text-paper">Fish</a></li>
      </ul>
    </div>
    <div>
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide" style="color: var(--peach);"><?= icon('document', 'h-4 w-4') ?> Store</p>
      <ul class="mt-3 space-y-2 text-sm text-paper/80">
        <li><a href="/services" class="hover:text-paper">Services</a></li>
        <li><a href="/blog" class="hover:text-paper">Blog</a></li>
        <li><a href="/faq" class="hover:text-paper">FAQ</a></li>
        <li><a href="/contact" class="hover:text-paper">Contact</a></li>
        <li><a href="/legal/privacy" class="hover:text-paper">Privacy</a></li>
      </ul>
    </div>
    <div class="rounded-2xl bg-white/8 backdrop-blur p-5 border border-white/10">
      <p class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide" style="color: var(--peach);"><?= icon('mail', 'h-4 w-4') ?> Newsletter</p>
      <p class="mt-3 text-sm text-paper/70">Reminders, offers, and the occasional good boy photo.</p>
      <form method="POST" action="/newsletter/subscribe" class="mt-3 flex gap-2">
        <?= csrf_field() ?>
        <label for="newsletter-email" class="sr-only">Email</label>
        <input id="newsletter-email" type="email" name="email" required placeholder="you@example.com"
               class="min-w-0 flex-1 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm placeholder:text-paper/40 focus:border-white/60 outline-none">
        <button type="submit" class="btn btn-sm shrink-0">Join</button>
      </form>
    </div>
  </div>
  <div class="relative border-t border-white/10 py-4 text-center text-xs text-paper/60">
    &copy; <?= date('Y') ?> Happy Tails Pet Store. All rights reserved.
  </div>
</footer>

<?php $hasStickyCartBar = str_starts_with(request()->path(), '/shop/'); ?>
<a href="https://wa.me/910000000000" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
   class="pulse-ring soft-drift fixed <?= $hasStickyCartBar ? 'bottom-24 lg:bottom-5' : 'bottom-5' ?> right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full text-paper shadow-lg hover:scale-110 transition-transform duration-150"
   style="background: linear-gradient(135deg, var(--mint), var(--skyb)); box-shadow: 0 12px 28px -8px rgba(16,185,129,0.5);">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 6.3A8.9 8.9 0 0 0 12 4a9 9 0 0 0-7.9 13.4L3 21l3.7-1a9 9 0 0 0 5.3 1.7 9 9 0 0 0 9-9 8.9 8.9 0 0 0-2.4-6.4Zm-5.6 13.8a7.5 7.5 0 0 1-3.8-1l-.3-.2-2.2.6.6-2.2-.2-.3a7.5 7.5 0 1 1 6 3.1Zm4.1-5.6c-.2-.1-1.3-.7-1.5-.7-.2-.1-.3-.1-.5.1s-.6.7-.8.9-.3.2-.6 0a6.1 6.1 0 0 1-1.8-1.1 6.7 6.7 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.5l.4-.4.2-.4a.5.5 0 0 0 0-.4c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.6 10.6 0 0 0 4.1 3.7 4.7 4.7 0 0 0 2.9.6 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c0-.2-.2-.2-.4-.4Z"/></svg>
</a>

<div x-data="{ visible: !localStorage.getItem('petshop_cookie_consent') }" x-show="visible" x-cloak x-transition.origin.bottom
     class="glass fixed inset-x-3 sm:inset-x-6 <?= $hasStickyCartBar ? 'bottom-24 lg:bottom-3' : 'bottom-3' ?> z-50 rounded-3xl px-5 py-4 sm:px-6" style="background: rgba(36, 27, 63, 0.85); border-color: rgba(255,255,255,0.15);">
  <div class="mx-auto max-w-5xl flex flex-col sm:flex-row items-center justify-between gap-3">
    <p class="flex items-center gap-3 text-sm text-paper/85">
      <span class="icon-chip icon-chip-plum shrink-0"><?= icon('info', 'h-4 w-4') ?></span>
      We use cookies to keep your cart working and to understand how the store is used. See our <a href="/legal/privacy" class="underline">privacy policy</a>.
    </p>
    <button type="button" @click="localStorage.setItem('petshop_cookie_consent','1'); visible = false"
            class="btn btn-sm shrink-0">Got it</button>
  </div>
</div>

<div x-data="{ toasts: [] }"
     x-init="window.addEventListener('petshop-toast', (e) => {
       const id = Date.now() + Math.random();
       toasts.push({ id, type: e.detail.type ?? 'success', message: e.detail.message });
       setTimeout(() => { toasts = toasts.filter((t) => t.id !== id); }, 3000);
     })"
     class="fixed top-20 inset-x-3 sm:inset-x-auto sm:right-4 z-[60] flex flex-col items-center sm:items-end gap-2 pointer-events-none"
     aria-live="polite">
  <template x-for="t in toasts" :key="t.id">
    <div class="glass pointer-events-auto rounded-2xl px-4 py-3 text-sm font-medium flex items-center gap-2.5 max-w-sm"
         :class="t.type === 'error' ? 'text-[#7a1a12]' : 'text-[#0b3d2e]'"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
      <span class="icon-chip !w-7 !h-7 shrink-0" :class="t.type === 'error' ? 'icon-chip-leash' : 'icon-chip-fern'">
        <svg x-show="t.type !== 'error'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        <svg x-show="t.type === 'error'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L1 21h22L12 2z"/><line x1="12" y1="9" x2="12" y2="14"/><circle cx="12" cy="17.3" r="0.9" fill="currentColor" stroke="none"/></svg>
      </span>
      <span x-text="t.message"></span>
    </div>
  </template>
</div>

</body>
</html>
