<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $coupons */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold border-b-2 border-ink pb-1">Coupons</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="border-b-2 border-ink">
          <th class="p-2 text-left">Code</th><th class="p-2 text-left">Type</th><th class="p-2 text-right">Value</th>
          <th class="p-2 text-right">Used</th><th class="p-2 text-left">Auto?</th><th class="p-2 text-left">Active</th><th class="p-2"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($coupons as $c): ?>
          <tr class="border-b border-mist">
            <td class="p-2 font-mono font-medium"><?= e($c['code']) ?></td>
            <td class="p-2 capitalize"><?= e($c['type']) ?></td>
            <td class="p-2 text-right"><?= $c['type'] === 'percent' ? $c['value'] . '%' : money((int) $c['value']) ?></td>
            <td class="p-2 text-right"><?= (int) $c['usage_count'] ?><?= $c['usage_limit'] ? ' / ' . (int) $c['usage_limit'] : '' ?></td>
            <td class="p-2"><?= $c['auto_generated'] ? 'Agent' : 'Manual' ?></td>
            <td class="p-2"><?= $c['is_active'] ? 'Yes' : 'No' ?></td>
            <td class="p-2">
              <form method="POST" action="/admin/marketing/coupons/<?= (int) $c['id'] ?>/toggle">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs font-semibold text-leash hover:underline"><?= $c['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($coupons === []): ?>
          <tr><td colspan="7" class="p-6 text-center text-ink/50">No coupons yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">New coupon</p>
    <form method="POST" action="/admin/marketing/coupons" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="code" placeholder="CODE" required class="uppercase input text-sm">
      <input type="text" name="description" placeholder="Description" class="input text-sm">
      <div class="flex gap-2">
        <select name="type" class="flex-1 input text-sm">
          <option value="percent">% off</option>
          <option value="fixed">₹ off</option>
        </select>
        <input type="number" step="0.01" name="value" placeholder="Value" required class="flex-1 input text-sm">
      </div>
      <input type="number" step="0.01" name="min_order" placeholder="Min order ₹ (optional)" class="input text-sm">
      <div class="flex gap-2">
        <input type="number" name="usage_limit" placeholder="Total uses" class="flex-1 input text-sm">
        <input type="number" name="usage_limit_per_customer" placeholder="Per customer" class="flex-1 input text-sm">
      </div>
      <input type="date" name="expires_at" class="input text-sm">
      <button type="submit" class="w-full btn btn-secondary btn-sm">Create</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
