<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $subscribers */
/** @var int $subscribedCount */
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-4">
  <div class="flex gap-2">
    <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
    <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
    <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
    <a href="/admin/marketing/gift-cards" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Gift cards</a>
    <a href="/admin/marketing/newsletter" class="text-sm font-semibold border-b-2 border-ink pb-1">Newsletter</a>
    <a href="/admin/marketing/referrals" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Referrals</a>
    <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
  </div>
  <a href="/admin/marketing/newsletter/export" class="btn btn-secondary btn-sm">Export CSV</a>
</div>

<p class="text-sm text-ink/60 mb-4"><?= $subscribedCount ?> subscribed of <?= count($subscribers) ?> total.</p>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Email</th><th class="p-2 text-left">Status</th><th class="p-2 text-left">Subscribed</th></tr></thead>
    <tbody>
      <?php foreach ($subscribers as $s): ?>
        <tr class="border-b border-mist">
          <td class="p-2 font-medium"><?= e($s['email']) ?></td>
          <td class="p-2"><span class="badge <?= $s['status'] === 'subscribed' ? 'badge-success' : 'badge-neutral' ?> capitalize"><?= e($s['status']) ?></span></td>
          <td class="p-2 text-ink/50"><?= e(date('d M Y', strtotime((string) $s['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($subscribers === []): ?><tr><td colspan="3" class="p-6 text-center text-ink/50">No subscribers yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
