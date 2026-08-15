<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($title) ? e($title) . ' — ' : '' ?>Admin — <?= e(config('app.name')) ?></title>
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/build/tailwind.css">
<link rel="stylesheet" href="/assets/css/app.css">
<style>:root { --leash: <?= e(theme_accent_hex()) ?>; --danger: #dc2626; --info: #2563eb; --plum: #7c3aed; --sky: #2f7fb8; } [x-cloak] { display: none !important; }</style>
<script defer src="/assets/js/alpine.min.js"></script>
<script defer src="/assets/js/count-up.js"></script>
</head>
<body class="bg-mist/40 text-ink font-sans antialiased text-sm">

<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

  <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
         class="fixed lg:static inset-y-0 left-0 z-40 w-64 shrink-0 bg-gradient-to-b from-ink to-[#1c1f2e] text-paper transition-transform duration-150 flex flex-col">
    <div class="h-16 flex items-center gap-2.5 px-5 border-b border-paper/10">
      <span class="icon-chip !bg-tennis !text-ink"><?= icon('paw', 'h-5 w-5') ?></span>
      <a href="/admin" class="font-display text-lg font-bold leading-tight">Happy Tails <span class="block text-xs font-semibold text-tennis tracking-wide">ADMIN</span></a>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" aria-label="Admin">
      <?php
        $adminUser = auth()->user();
        $adminRoleSlug = $adminUser
          ? (string) (\App\Core\Database::instance()->selectOne('SELECT slug FROM roles WHERE id = :id', ['id' => $adminUser['role_id']])['slug'] ?? 'staff')
          : 'staff';
        $currentPath = request()->path();
        $navItems = [
          ['href' => '/admin', 'icon' => 'dashboard', 'label' => 'Dashboard', 'match' => '/admin', 'exact' => true],
          ['href' => '/admin/catalogue', 'icon' => 'box', 'label' => 'Catalogue', 'match' => '/admin/catalogue'],
          ['href' => '/admin/inventory', 'icon' => 'layers', 'label' => 'Inventory', 'match' => '/admin/inventory'],
          ['href' => '/admin/orders', 'icon' => 'cart', 'label' => 'Orders', 'match' => '/admin/orders'],
          ['href' => '/admin/customers', 'icon' => 'users', 'label' => 'Customers', 'match' => '/admin/customers'],
          ['href' => '/admin/support', 'icon' => 'life-buoy', 'label' => 'Support', 'match' => '/admin/support'],
          ['href' => '/admin/services', 'icon' => 'calendar', 'label' => 'Services', 'match' => '/admin/services'],
          ['href' => '/admin/marketing', 'icon' => 'megaphone', 'label' => 'Marketing', 'match' => '/admin/marketing'],
          ['href' => '/admin/content', 'icon' => 'document', 'label' => 'Content', 'match' => '/admin/content'],
          ['href' => '/admin/reports', 'icon' => 'chart-bar', 'label' => 'Reports', 'match' => '/admin/reports'],
          ['href' => '/admin/settings', 'icon' => 'cog', 'label' => 'Settings', 'match' => '/admin/settings'],
        ];
      ?>
      <?php foreach ($navItems as $item): ?>
        <?php $isActive = $item['exact'] ?? false ? $currentPath === $item['match'] : str_starts_with($currentPath, $item['match']); ?>
        <a href="<?= e($item['href']) ?>"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors duration-150 <?= $isActive ? 'bg-paper/10 text-paper font-semibold' : 'text-paper/70 hover:bg-paper/5 hover:text-paper' ?>">
          <span class="icon-chip !w-8 !h-8 !rounded-md transition-colors duration-150 <?= $isActive ? 'bg-leash text-paper' : 'bg-paper/10 text-paper/60 group-hover:text-paper' ?>"><?= icon($item['icon'], 'h-4 w-4') ?></span>
          <span class="text-sm"><?= e($item['label']) ?></span>
        </a>
      <?php endforeach; ?>
      <?php if ($adminUser && \App\Core\App::auth()->hasRole('developer', 'owner') && config('developer_tools')): ?>
        <?php $devActive = str_starts_with($currentPath, '/admin/dev'); ?>
        <div class="pt-3 mt-3 border-t border-paper/10">
          <p class="px-3 pb-1 text-xs uppercase tracking-wide text-paper/40">Developer</p>
          <a href="/admin/dev"
             class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors duration-150 <?= $devActive ? 'bg-paper/10 text-paper font-semibold' : 'text-paper/70 hover:bg-paper/5 hover:text-paper' ?>">
            <span class="icon-chip !w-8 !h-8 !rounded-md transition-colors duration-150 <?= $devActive ? 'bg-leash text-paper' : 'bg-paper/10 text-paper/60 group-hover:text-paper' ?>"><?= icon('terminal', 'h-4 w-4') ?></span>
            <span class="text-sm">Developer tools</span>
          </a>
        </div>
      <?php endif; ?>
    </nav>
    <div class="px-3 py-4 border-t border-paper/10 flex items-center gap-2.5">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-tennis text-ink text-xs font-bold"><?= e(mb_strtoupper(mb_substr((string) ($adminUser['name'] ?? '?'), 0, 1))) ?></span>
      <div class="min-w-0">
        <p class="text-xs text-paper truncate"><?= e($adminUser['name'] ?? '') ?></p>
        <p class="text-[11px] text-paper/50 truncate"><?= e(ucfirst($adminRoleSlug)) ?></p>
      </div>
    </div>
  </aside>

  <div class="flex-1 min-w-0 flex flex-col">
    <header class="h-16 bg-paper border-b-2 border-ink flex items-center justify-between px-4 sm:px-6">
      <div class="flex items-center gap-3">
        <button class="lg:hidden" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <h1 class="font-display text-lg font-semibold"><?= e($title ?? 'Dashboard') ?></h1>
      </div>
      <div class="flex items-center gap-3">
        <span class="badge badge-info hidden sm:inline-flex"><?= icon('shield-check', 'h-3 w-3') ?> <?= e(ucfirst($adminRoleSlug)) ?></span>
        <form method="POST" action="/admin/logout">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-ghost !px-3 !py-2 text-sm font-medium"><?= icon('arrow-right', 'h-4 w-4') ?> Sign out</button>
        </form>
      </div>
    </header>

    <?php
      $adminFlashes = [];
      foreach (['success', 'error'] as $flashType) {
        foreach (\App\Core\Session::getFlash($flashType) as $message) {
          $adminFlashes[] = ['type' => $flashType, 'message' => $message];
        }
      }
    ?>
    <div x-data="{ toasts: [] }"
         x-init="
           (<?= e(json_encode($adminFlashes)) ?>).forEach((f) => {
             const id = Date.now() + Math.random();
             toasts.push({ id, ...f });
             setTimeout(() => { toasts = toasts.filter((t) => t.id !== id); }, 5000);
           });
           window.addEventListener('admin-toast', (e) => {
             const id = Date.now() + Math.random();
             toasts.push({ id, type: e.detail.type ?? 'success', message: e.detail.message });
             setTimeout(() => { toasts = toasts.filter((t) => t.id !== id); }, 5000);
           });
         "
         class="fixed top-4 inset-x-3 sm:inset-x-auto sm:right-4 z-[60] flex flex-col items-center sm:items-end gap-2 pointer-events-none"
         aria-live="polite">
      <template x-for="t in toasts" :key="t.id">
        <div class="pointer-events-auto border-2 px-4 py-3 text-sm font-medium flex items-center gap-2.5 max-w-sm bg-paper"
             :class="t.type === 'error' ? 'border-leash text-leash' : 'border-fern text-fern'"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
          <svg x-show="t.type !== 'error'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
          <svg x-show="t.type === 'error'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M12 2L1 21h22L12 2z"/><line x1="12" y1="9" x2="12" y2="14"/><circle cx="12" cy="17.3" r="0.9" fill="currentColor" stroke="none"/></svg>
          <span x-text="t.message" class="text-ink"></span>
          <button type="button" @click="toasts = toasts.filter((x) => x.id !== t.id)" aria-label="Dismiss" class="ml-auto shrink-0 text-ink/40 hover:text-ink">&times;</button>
        </div>
      </template>
    </div>

    <main class="flex-1 p-4 sm:p-6">
      <?= \App\Core\View::section('content') ?>
    </main>
  </div>
</div>

<script>
  // Guards every POST form against double-submission (refunds, stock
  // adjustments, deletes) — disables the button that triggered submit so a
  // slow request or an impatient double-click can't fire it twice. GET
  // forms (filters/search) are left alone since re-submitting those is
  // harmless and some rely on repeat auto-submits (onchange="this.form.submit()").
  document.addEventListener('submit', (e) => {
    const form = e.target;
    // e.defaultPrevented catches forms cancelled by onsubmit="return confirm(...)" —
    // without this check, clicking Cancel on a delete confirmation would still
    // leave the button permanently disabled even though nothing was submitted.
    if (!(form instanceof HTMLFormElement) || form.method.toUpperCase() !== 'POST' || e.defaultPrevented) {
      return;
    }
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((btn) => {
      if (btn.disabled) {
        e.preventDefault();
        return;
      }
      requestAnimationFrame(() => {
        btn.disabled = true;
        btn.classList.add('opacity-60', 'cursor-wait');
      });
    });
  });
</script>

</body>
</html>
