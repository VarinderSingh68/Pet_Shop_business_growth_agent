<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var int $balance */
/** @var string $tier */
/** @var array $ledger */
/** @var string $referralCode */
/** @var array $referrals */

$referralUrl = url('/?ref=' . $referralCode);
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'rewards']); ?>

    <div>
      <h1 class="font-display text-3xl font-bold">Rewards</h1>

      <div class="mt-6 grid sm:grid-cols-2 gap-5">
        <div class="card-tag p-6">
          <div class="card-tag__tab"><?= e($tier) ?></div>
          <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold">Loyalty points</p>
          <p class="font-display text-4xl font-bold mt-1"><?= $balance ?></p>
          <p class="text-sm text-ink/60 mt-2">Worth <?= money($balance * 100) ?> off your next order. Redeem at checkout.</p>
          <p class="text-xs text-ink/40 mt-2">Earn 1 point per ₹10 spent. Points expire 12 months after they're earned.</p>
        </div>

        <div class="card-tag p-6">
          <div class="card-tag__tab">Refer a friend</div>
          <p class="text-xs uppercase tracking-wide text-ink/50 font-semibold">Your referral link</p>
          <p class="font-mono text-sm mt-2 break-all bg-mist/40 p-2"><?= e($referralUrl) ?></p>
          <p class="text-sm text-ink/60 mt-3">You and your friend each get ₹150 in loyalty points when they place their first order.</p>
        </div>
      </div>

      <div class="mt-8">
        <h2 class="font-display text-lg font-semibold">Points history</h2>
        <?php if ($ledger === []): ?>
          <p class="mt-2 text-sm text-ink/60">No point activity yet — place an order to start earning.</p>
        <?php else: ?>
          <ul class="mt-3 space-y-2 text-sm">
            <?php foreach ($ledger as $entry): ?>
              <li class="flex justify-between border-b border-mist pb-2">
                <span class="capitalize"><?= e($entry['type']) ?><?= $entry['note'] ? ' — ' . e($entry['note']) : '' ?></span>
                <span class="font-semibold <?= $entry['points'] > 0 ? 'text-fern' : 'text-leash' ?>"><?= $entry['points'] > 0 ? '+' : '' ?><?= (int) $entry['points'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <?php if ($referrals !== []): ?>
        <div class="mt-8">
          <h2 class="font-display text-lg font-semibold">Your referrals</h2>
          <ul class="mt-3 space-y-2 text-sm">
            <?php foreach ($referrals as $r): ?>
              <li class="flex justify-between border-b border-mist pb-2">
                <span><?= e($r['referred_name']) ?></span>
                <span class="badge badge-info capitalize"><?= e(str_replace('_', ' ', $r['status'])) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
