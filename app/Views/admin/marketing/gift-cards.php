<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $giftCards */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
  <a href="/admin/marketing/gift-cards" class="text-sm font-semibold border-b-2 border-ink pb-1">Gift cards</a>
  <a href="/admin/marketing/newsletter" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Newsletter</a>
  <a href="/admin/marketing/referrals" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Referrals</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
</div>

<div class="mb-4 border-2 border-tennis bg-tennis/10 px-4 py-3 text-sm">
  Gift cards aren't wired into online checkout yet — issue them here and use the "Redeem" action to log balance used against phone/in-store orders.
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="border-b-2 border-ink">
          <th class="p-2 text-left">Code</th><th class="p-2 text-left">Recipient</th><th class="p-2 text-right">Balance</th>
          <th class="p-2 text-left">Active</th><th class="p-2 text-left">Expires</th><th class="p-2"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($giftCards as $g): ?>
          <tr class="border-b border-mist align-top">
            <td class="p-2 font-mono font-medium"><?= e($g['code']) ?></td>
            <td class="p-2"><?= e($g['recipient_name'] ?? '—') ?><?php if ($g['recipient_email']): ?><br><span class="text-xs text-ink/50"><?= e($g['recipient_email']) ?></span><?php endif; ?></td>
            <td class="p-2 text-right"><?= money((int) $g['balance_paise']) ?> <span class="text-xs text-ink/40">/ <?= money((int) $g['initial_balance_paise']) ?></span></td>
            <td class="p-2"><?= $g['is_active'] ? 'Yes' : 'No' ?></td>
            <td class="p-2 text-ink/50"><?= $g['expires_at'] ? e(date('d M Y', strtotime((string) $g['expires_at']))) : '—' ?></td>
            <td class="p-2 whitespace-nowrap space-y-1.5">
              <?php if ((int) $g['balance_paise'] > 0): ?>
                <form method="POST" action="/admin/marketing/gift-cards/<?= (int) $g['id'] ?>/redeem" class="flex items-center gap-1" onsubmit="return this.amount.value > 0;">
                  <?= csrf_field() ?>
                  <input type="number" name="amount" step="0.01" min="0.01" max="<?= (int) $g['balance_paise'] / 100 ?>" placeholder="Amount" class="input text-xs !py-1 w-20">
                  <button type="submit" class="text-xs font-semibold text-fern hover:underline">Redeem</button>
                </form>
              <?php endif; ?>
              <form method="POST" action="/admin/marketing/gift-cards/<?= (int) $g['id'] ?>/toggle">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs font-semibold text-leash hover:underline"><?= $g['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ($giftCards === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No gift cards issued yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Issue a gift card</p>
    <form method="POST" action="/admin/marketing/gift-cards" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <div>
        <label for="amount" class="block text-xs font-semibold mb-1">Amount (₹)</label>
        <input id="amount" type="number" name="amount" step="0.01" min="0.01" required class="input text-sm w-full">
      </div>
      <div>
        <label for="recipient_name" class="block text-xs font-semibold mb-1">Recipient name</label>
        <input id="recipient_name" type="text" name="recipient_name" class="input text-sm w-full">
      </div>
      <div>
        <label for="recipient_email" class="block text-xs font-semibold mb-1">Recipient email</label>
        <input id="recipient_email" type="email" name="recipient_email" class="input text-sm w-full">
      </div>
      <div>
        <label for="expires_at" class="block text-xs font-semibold mb-1">Expires (optional)</label>
        <input id="expires_at" type="date" name="expires_at" class="input text-sm w-full">
      </div>
      <div>
        <label for="note" class="block text-xs font-semibold mb-1">Note</label>
        <textarea id="note" name="note" rows="2" class="input text-sm w-full"></textarea>
      </div>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Issue gift card</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
