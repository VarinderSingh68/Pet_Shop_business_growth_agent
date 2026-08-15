<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $actions */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
  <a href="/admin/marketing/gift-cards" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Gift cards</a>
  <a href="/admin/marketing/newsletter" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Newsletter</a>
  <a href="/admin/marketing/referrals" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Referrals</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold border-b-2 border-ink pb-1">Growth Agent activity</a>
</div>

<p class="text-sm text-ink/60 mb-4">Every action the Growth Agent takes, with a plain-English reason. Runs every 15 minutes via <code>cron.php</code>.</p>

<div class="border-2 border-ink bg-white overflow-x-auto">
  <table class="admin-table w-full text-sm">
    <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">When</th><th class="p-2 text-left">Type</th><th class="p-2 text-left">What happened</th><th class="p-2 text-right">Affected</th></tr></thead>
    <tbody>
      <?php foreach ($actions as $a): ?>
        <tr class="border-b border-mist">
          <td class="p-2 text-ink/50 whitespace-nowrap"><?= e($a['created_at']) ?></td>
          <td class="p-2"><span class="badge badge-info capitalize"><?= e(str_replace('_', ' ', $a['action_type'])) ?></span></td>
          <td class="p-2"><?= e($a['explanation']) ?></td>
          <td class="p-2 text-right"><?= $a['affected_count'] !== null ? (int) $a['affected_count'] : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($actions === []): ?>
        <tr><td colspan="4" class="p-6 text-center text-ink/50">No Growth Agent activity yet. Run <code>php cron.php</code>.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php \App\Core\View::stop(); ?>
