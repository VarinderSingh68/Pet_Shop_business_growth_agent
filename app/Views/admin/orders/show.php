<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $order */
/** @var array|null $customer */
/** @var array $items */
/** @var array $history */
/** @var array $payments */
/** @var array $refunds */
/** @var array|null $shipment */
/** @var array $statuses */
?>

<a href="/admin/orders" class="text-sm text-ink/50 hover:text-leash">&larr; Orders</a>

<div class="mt-2 flex items-center justify-between flex-wrap gap-3">
  <h1 class="font-display text-2xl font-semibold">Order <?= e($order['order_number']) ?></h1>
  <a href="/admin/orders/<?= (int) $order['id'] ?>/invoice" target="_blank" class="btn btn-secondary btn-sm">Print invoice</a>
</div>

<div class="mt-6 grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="space-y-6">
    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Items</p>
      <table class="w-full text-sm mt-3">
        <?php foreach ($items as $item): ?>
          <tr class="border-b border-mist">
            <td class="p-2"><?= e($item['product_name_snapshot']) ?> <span class="text-ink/50">(<?= e($item['variant_label_snapshot']) ?>)</span></td>
            <td class="p-2 text-right text-ink/60">&times;<?= (int) $item['quantity'] ?></td>
            <td class="p-2 text-right font-semibold"><?= money((int) $item['line_total_paise']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <dl class="mt-4 pt-4 border-t-2 border-mist space-y-1 text-sm max-w-xs ml-auto">
        <div class="flex justify-between"><dt class="text-ink/60">Subtotal</dt><dd><?= money((int) $order['subtotal_paise']) ?></dd></div>
        <?php if ((int) $order['discount_paise'] > 0): ?><div class="flex justify-between text-fern"><dt>Discount</dt><dd>&minus;<?= money((int) $order['discount_paise']) ?></dd></div><?php endif; ?>
        <div class="flex justify-between"><dt class="text-ink/60">Shipping</dt><dd><?= money((int) $order['shipping_paise']) ?></dd></div>
        <div class="flex justify-between"><dt class="text-ink/60">Tax</dt><dd><?= money((int) $order['tax_paise']) ?></dd></div>
        <div class="flex justify-between font-bold font-display text-base pt-1"><dt>Total</dt><dd><?= money((int) $order['total_paise']) ?></dd></div>
      </dl>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Status timeline</p>
      <ul class="mt-3 space-y-2 text-sm">
        <?php foreach (array_reverse($history) as $h): ?>
          <li class="flex justify-between border-b border-mist pb-2">
            <span class="capitalize"><?= e(str_replace('_', ' ', $h['status'])) ?><?= $h['note'] ? ' — ' . e($h['note']) : '' ?></span>
            <span class="text-ink/40"><?= e($h['created_at']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/status" class="mt-4 flex flex-wrap items-end gap-2">
        <?= csrf_field() ?>
        <div>
          <label for="status" class="block text-xs font-semibold mb-1">Update status</label>
          <select id="status" name="status" class="input text-sm">
            <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <input type="text" name="note" placeholder="Note (optional)" class="flex-1 min-w-[160px] input text-sm">
        <button type="submit" class="btn btn-secondary btn-sm">Update</button>
      </form>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Refunds</p>
      <?php if ($refunds !== []): ?>
        <ul class="mt-3 space-y-2 text-sm">
          <?php foreach ($refunds as $r): ?>
            <li class="flex justify-between border-b border-mist pb-2">
              <span><?= money((int) $r['amount_paise']) ?><?= $r['reason'] ? ' — ' . e($r['reason']) : '' ?></span>
              <span class="text-ink/40"><?= e($r['created_at']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/refund" class="mt-4 flex flex-wrap items-end gap-2">
        <?= csrf_field() ?>
        <div>
          <label for="amount" class="block text-xs font-semibold mb-1">Refund amount (₹)</label>
          <input id="amount" type="number" step="0.01" name="amount" max="<?= (int) $order['total_paise'] / 100 ?>" class="w-32 input text-sm">
        </div>
        <input type="text" name="reason" placeholder="Reason" class="flex-1 min-w-[160px] input text-sm">
        <button type="submit" class="border-2 border-leash text-leash px-4 py-1.5 text-sm font-semibold hover:bg-leash hover:text-paper">Issue refund</button>
      </form>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Internal notes</p>
      <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/note" class="mt-3">
        <?= csrf_field() ?>
        <textarea name="internal_notes" rows="3" class="input text-sm"><?= e($order['internal_notes'] ?? '') ?></textarea>
        <button type="submit" class="mt-2 btn btn-secondary btn-sm">Save note</button>
      </form>
    </div>
  </div>

  <div class="space-y-4">
    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Customer</p>
      <?php if ($customer !== null): ?>
        <a href="/admin/customers/<?= (int) $customer['id'] ?>" class="text-sm font-semibold text-leash hover:underline"><?= e($customer['name']) ?></a>
        <p class="text-sm text-ink/60"><?= e($customer['email']) ?></p>
      <?php else: ?>
        <p class="text-sm"><?= e($order['guest_email']) ?> <span class="text-ink/50">(guest)</span></p>
      <?php endif; ?>
      <p class="text-sm text-ink/60 mt-1"><?= e($order['shipping_phone']) ?></p>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Shipping address</p>
      <p class="text-sm mt-2"><?= e($order['shipping_full_name']) ?><br>
        <?= e($order['shipping_line1']) ?><?= $order['shipping_line2'] ? ', ' . e($order['shipping_line2']) : '' ?><br>
        <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> <?= e($order['shipping_postal_code']) ?></p>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Shipment</p>
      <form method="POST" action="/admin/orders/<?= (int) $order['id'] ?>/shipment" class="mt-3 space-y-2">
        <?= csrf_field() ?>
        <div>
          <label for="carrier" class="block text-xs font-semibold mb-1">Carrier</label>
          <input id="carrier" type="text" name="carrier" value="<?= e($shipment['carrier'] ?? '') ?>" placeholder="e.g. Delhivery" class="input text-sm w-full">
        </div>
        <div>
          <label for="tracking_number" class="block text-xs font-semibold mb-1">Tracking number</label>
          <input id="tracking_number" type="text" name="tracking_number" value="<?= e($shipment['tracking_number'] ?? '') ?>" class="input text-sm w-full">
        </div>
        <div>
          <label for="shipment_status" class="block text-xs font-semibold mb-1">Shipment status</label>
          <select id="shipment_status" name="shipment_status" class="input text-sm w-full">
            <?php foreach (['pending', 'packed', 'shipped', 'delivered', 'returned'] as $st): ?>
              <option value="<?= $st ?>" <?= ($shipment['status'] ?? 'pending') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="w-full btn btn-secondary btn-sm">Save shipment</button>
      </form>
      <?php if ($shipment !== null && $shipment['shipped_at']): ?><p class="text-xs text-ink/50 mt-2">Shipped <?= e($shipment['shipped_at']) ?></p><?php endif; ?>
      <?php if ($shipment !== null && $shipment['delivered_at']): ?><p class="text-xs text-ink/50">Delivered <?= e($shipment['delivered_at']) ?></p><?php endif; ?>
    </div>

    <div class="card-tag p-5 bg-white">
      <p class="font-display font-semibold">Payment</p>
      <p class="text-sm mt-2 capitalize"><?= e($order['payment_method']) ?> &middot; <?= e(str_replace('_', ' ', $order['payment_status'])) ?></p>
      <?php foreach ($payments as $p): ?>
        <p class="text-xs text-ink/50 mt-1"><?= e($p['gateway']) ?> &middot; <?= e($p['status']) ?> &middot; <?= money((int) $p['amount_paise']) ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
