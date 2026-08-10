<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $items */
/** @var array $totals */
/** @var array|null $user */
/** @var array $addresses */
/** @var bool $razorpayConfigured */
/** @var int $loyaltyBalance */

$defaultAddress = $addresses[0] ?? null;
?>

<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-3xl font-bold">Checkout</h1>

  <div class="mt-5 flex items-center gap-2 text-sm font-semibold text-ink/40">
    <span class="flex items-center gap-1.5 text-fern"><?= icon('check', 'h-4 w-4') ?> Cart</span>
    <span class="h-px w-8 bg-mist"></span>
    <span class="flex items-center gap-1.5 text-ink"><span class="icon-chip icon-chip-leash !w-6 !h-6 !rounded-full"><?= icon('credit-card', 'h-3.5 w-3.5') ?></span> Checkout</span>
    <span class="h-px w-8 bg-mist"></span>
    <span class="flex items-center gap-1.5"><?= icon('check', 'h-4 w-4') ?> Confirmation</span>
  </div>

  <form method="POST" action="/checkout" class="mt-8 grid lg:grid-cols-[1fr_320px] gap-10">
    <?= csrf_field() ?>

    <div class="space-y-8">
      <?php if ($user === null): ?>
        <div>
          <h2 class="flex items-center gap-2 font-display text-lg font-semibold mb-3"><?= icon('mail', 'h-4 w-4 text-ink/40') ?> Contact</h2>
          <label for="guest_email" class="block text-sm font-semibold mb-1">Email</label>
          <input id="guest_email" type="email" name="guest_email" required value="<?= e(old('guest_email')) ?>"
                 class="input">
          <p class="mt-1 text-xs text-ink/50">We'll send your order confirmation here. <a href="/account/login" class="text-leash hover:underline">Have an account? Sign in</a> to check out faster.</p>
        </div>
      <?php endif; ?>

      <div>
        <h2 class="flex items-center gap-2 font-display text-lg font-semibold mb-3"><?= icon('map-pin', 'h-4 w-4 text-ink/40') ?> Delivery address</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label for="full_name" class="block text-sm font-semibold mb-1">Full name</label>
            <input id="full_name" type="text" name="full_name" required value="<?= e(old('full_name', $defaultAddress['full_name'] ?? '')) ?>" class="input">
          </div>
          <div>
            <label for="phone" class="block text-sm font-semibold mb-1">Phone</label>
            <input id="phone" type="tel" name="phone" required value="<?= e(old('phone', $defaultAddress['phone'] ?? '')) ?>" class="input">
          </div>
          <div>
            <label for="postal_code" class="block text-sm font-semibold mb-1">Postal code</label>
            <input id="postal_code" type="text" name="postal_code" required value="<?= e(old('postal_code', $defaultAddress['postal_code'] ?? '')) ?>" class="input">
          </div>
          <div class="sm:col-span-2">
            <label for="line1" class="block text-sm font-semibold mb-1">Address line 1</label>
            <input id="line1" type="text" name="line1" required value="<?= e(old('line1', $defaultAddress['line1'] ?? '')) ?>" class="input">
          </div>
          <div class="sm:col-span-2">
            <label for="line2" class="block text-sm font-semibold mb-1">Address line 2 <span class="font-normal text-ink/50">(optional)</span></label>
            <input id="line2" type="text" name="line2" value="<?= e(old('line2', $defaultAddress['line2'] ?? '')) ?>" class="input">
          </div>
          <div>
            <label for="city" class="block text-sm font-semibold mb-1">City</label>
            <input id="city" type="text" name="city" required value="<?= e(old('city', $defaultAddress['city'] ?? '')) ?>" class="input">
          </div>
          <div>
            <label for="state" class="block text-sm font-semibold mb-1">State</label>
            <input id="state" type="text" name="state" required value="<?= e(old('state', $defaultAddress['state'] ?? '')) ?>" class="input">
          </div>
        </div>
      </div>

      <div>
        <h2 class="flex items-center gap-2 font-display text-lg font-semibold mb-3"><?= icon('credit-card', 'h-4 w-4 text-ink/40') ?> Payment</h2>
        <div class="space-y-2">
          <label class="flex items-center gap-3 border-2 border-ink px-4 py-3 cursor-pointer has-[:checked]:border-leash has-[:checked]:bg-leash/5">
            <input type="radio" name="payment_method" value="cod" checked class="border-2 border-ink">
            <span class="icon-chip icon-chip-fern !w-8 !h-8"><?= icon('truck', 'h-4 w-4') ?></span>
            <span class="font-medium">Cash on delivery</span>
          </label>
          <label class="flex items-center gap-3 border-2 px-4 py-3 <?= $razorpayConfigured ? 'border-ink cursor-pointer has-[:checked]:border-leash has-[:checked]:bg-leash/5' : 'border-mist text-ink/40 cursor-not-allowed' ?>">
            <input type="radio" name="payment_method" value="razorpay" <?= $razorpayConfigured ? '' : 'disabled' ?> class="border-2 border-ink">
            <span class="icon-chip <?= $razorpayConfigured ? 'icon-chip-info' : '!bg-mist !text-ink/40' ?> !w-8 !h-8"><?= icon('credit-card', 'h-4 w-4') ?></span>
            <span class="font-medium">Pay online (UPI, cards, netbanking)</span>
            <?php if (!$razorpayConfigured): ?><span class="text-xs">(not configured)</span><?php endif; ?>
          </label>
        </div>
      </div>

      <?php if ($loyaltyBalance > 0): ?>
        <div class="border-2 border-fern p-4 bg-fern/5">
          <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="redeem_points" value="1" class="mt-1 border-2 border-ink">
            <span class="icon-chip icon-chip-fern !w-8 !h-8"><?= icon('gift', 'h-4 w-4') ?></span>
            <span class="text-sm">
              <span class="font-semibold text-fern">Use my <?= $loyaltyBalance ?> loyalty points</span>
              <span class="block text-ink/60">Worth up to <?= money($loyaltyBalance * 100) ?> off this order.</span>
            </span>
          </label>
        </div>
      <?php endif; ?>

      <div>
        <label for="notes" class="block text-sm font-semibold mb-1">Delivery notes <span class="font-normal text-ink/50">(optional)</span></label>
        <textarea id="notes" name="notes" rows="2" class="input"><?= e(old('notes')) ?></textarea>
      </div>
    </div>

    <div class="card-tag p-6 h-fit">
      <div class="card-tag__tab">Order</div>
      <ul class="space-y-3 text-sm mb-4">
        <?php foreach ($items as $item): ?>
          <li class="flex justify-between">
            <span><?= e($item['product_name']) ?> <span class="text-ink/50">&times;<?= (int) $item['quantity'] ?></span></span>
            <span><?= money((int) $item['price_paise'] * (int) $item['quantity']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <dl class="space-y-2 text-sm border-t-2 border-mist pt-4">
        <div class="flex justify-between"><dt class="text-ink/60">Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
        <?php if ($totals['discount'] > 0): ?>
          <div class="flex justify-between text-fern"><dt>Discount</dt><dd>&minus;<?= money($totals['discount']) ?></dd></div>
        <?php endif; ?>
        <div class="flex justify-between"><dt class="text-ink/60">Shipping</dt><dd><?= $totals['shipping'] === 0 ? 'Free' : money($totals['shipping']) ?></dd></div>
        <div class="flex justify-between"><dt class="text-ink/60">Tax</dt><dd><?= money($totals['tax']) ?></dd></div>
      </dl>
      <div class="mt-4 pt-4 border-t-2 border-ink flex justify-between font-display text-lg font-bold">
        <span>Total</span><span><?= money($totals['total']) ?></span>
      </div>
      <button type="submit" class="btn btn-primary mt-6 w-full"><?= icon('shield-check', 'h-4 w-4') ?> Place order</button>
    </div>
  </form>
</section>

<?php \App\Core\View::stop(); ?>
