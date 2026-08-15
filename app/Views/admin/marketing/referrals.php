<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $referrals */
/** @var array $stats */

$statusColors = ['pending' => 'badge-neutral', 'completed' => 'badge-info', 'rewarded' => 'badge-success', 'fraud_flagged' => 'badge-danger'];
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
  <a href="/admin/marketing/gift-cards" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Gift cards</a>
  <a href="/admin/marketing/newsletter" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Newsletter</a>
  <a href="/admin/marketing/referrals" class="text-sm font-semibold border-b-2 border-ink pb-1">Referrals</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-5">
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase tracking-wide text-ink/50">Total referrals</p><p class="font-display text-2xl font-bold mt-1"><?= (int) ($stats['total'] ?? 0) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase tracking-wide text-ink/50">Rewarded</p><p class="font-display text-2xl font-bold mt-1"><?= (int) ($stats['rewarded'] ?? 0) ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase tracking-wide text-ink/50">Rewards paid</p><p class="font-display text-2xl font-bold mt-1"><?= money((int) ($stats['reward_total_paise'] ?? 0)) ?></p></div>
</div>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Referrer</th><th class="p-2 text-left">Referred</th><th class="p-2 text-left">Code</th><th class="p-2 text-left">Status</th><th class="p-2 text-right">Reward</th><th class="p-2 text-left">Date</th></tr></thead>
    <tbody>
      <?php foreach ($referrals as $r): ?>
        <tr class="border-b border-mist">
          <td class="p-2"><?= e($r['referrer_name']) ?><br><span class="text-xs text-ink/50"><?= e($r['referrer_email']) ?></span></td>
          <td class="p-2"><?= e($r['referred_name']) ?></td>
          <td class="p-2 font-mono"><?= e($r['code']) ?></td>
          <td class="p-2"><span class="badge <?= $statusColors[$r['status']] ?? 'badge-neutral' ?> capitalize"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
          <td class="p-2 text-right"><?= $r['reward_paise'] ? money((int) $r['reward_paise']) : '—' ?></td>
          <td class="p-2 text-ink/50"><?= e(date('d M Y', strtotime((string) $r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($referrals === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No referrals yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
