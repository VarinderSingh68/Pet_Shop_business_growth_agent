<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $segments */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold border-b-2 border-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
  <a href="/admin/marketing/gift-cards" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Gift cards</a>
  <a href="/admin/marketing/newsletter" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Newsletter</a>
  <a href="/admin/marketing/referrals" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Referrals</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
</div>

<p class="text-sm text-ink/60 mb-4">Segments are living — re-evaluated by the Growth Agent every 15 minutes. A customer who no longer qualifies drops out automatically.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach ($segments as $s): ?>
    <a href="/admin/marketing/segments/<?= (int) $s['id'] ?>" class="card-tag p-5 bg-white hover:border-leash">
      <p class="font-display text-lg font-semibold"><?= e($s['name']) ?></p>
      <p class="text-xs text-ink/50 mt-1"><?= e($s['description']) ?></p>
      <p class="font-display text-3xl font-bold mt-3"><?= (int) $s['member_count'] ?></p>
      <p class="text-xs text-ink/40">customers · updated <?= e($s['updated_at']) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<?php \App\Core\View::stop(); ?>
