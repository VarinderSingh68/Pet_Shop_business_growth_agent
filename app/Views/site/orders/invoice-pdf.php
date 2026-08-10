<?php
/** @var array $order */
/** @var array $items */
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Helvetica, Arial, sans-serif; color: #12141c; font-size: 12px; }
  h1 { font-size: 22px; margin: 0 0 4px; }
  .muted { color: #6b7280; }
  .header { display: table; width: 100%; margin-bottom: 24px; }
  .header .left, .header .right { display: table-cell; vertical-align: top; }
  .header .right { text-align: right; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  th, td { text-align: left; padding: 8px; border-bottom: 1px solid #dde3de; font-size: 11px; }
  th { text-transform: uppercase; letter-spacing: 0.05em; font-size: 10px; color: #6b7280; }
  .text-right { text-align: right; }
  .totals { width: 260px; margin-left: auto; margin-top: 16px; }
  .totals td { border: none; padding: 4px 8px; }
  .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #12141c; }
  .tag { display: inline-block; background: #f2b705; padding: 2px 8px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
</style>
</head>
<body>
  <div class="header">
    <div class="left">
      <h1>Happy Tails Pet Store</h1>
      <p class="muted">Bengaluru, Karnataka, India<br>GSTIN: 29ABCDE1234F1Z5</p>
    </div>
    <div class="right">
      <p class="tag">Invoice</p>
      <p><strong><?= e($order['order_number']) ?></strong><br>
      <?= date('d M Y', strtotime((string) $order['placed_at'])) ?></p>
    </div>
  </div>

  <div class="header">
    <div class="left">
      <p class="muted">Billed to</p>
      <p><?= e($order['shipping_full_name']) ?><br>
      <?= e($order['shipping_line1']) ?><?= $order['shipping_line2'] ? ', ' . e($order['shipping_line2']) : '' ?><br>
      <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> <?= e($order['shipping_postal_code']) ?><br>
      <?= e($order['shipping_phone']) ?></p>
    </div>
    <div class="right">
      <p class="muted">Payment method</p>
      <p><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Paid online (Razorpay)' ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr><th>Item</th><th>SKU</th><th class="text-right">Qty</th><th class="text-right">Unit price</th><th class="text-right">Total</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= e($item['product_name_snapshot']) ?><br><span class="muted"><?= e($item['variant_label_snapshot']) ?></span></td>
          <td><?= e($item['sku_snapshot']) ?></td>
          <td class="text-right"><?= (int) $item['quantity'] ?></td>
          <td class="text-right"><?= money((int) $item['unit_price_paise']) ?></td>
          <td class="text-right"><?= money((int) $item['line_total_paise']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="totals">
    <tr><td>Subtotal</td><td class="text-right"><?= money((int) $order['subtotal_paise']) ?></td></tr>
    <?php if ((int) $order['discount_paise'] > 0): ?>
      <tr><td>Discount<?= $order['coupon_code_snapshot'] ? ' (' . e($order['coupon_code_snapshot']) . ')' : '' ?></td><td class="text-right">&minus;<?= money((int) $order['discount_paise']) ?></td></tr>
    <?php endif; ?>
    <tr><td>Shipping</td><td class="text-right"><?= (int) $order['shipping_paise'] === 0 ? 'Free' : money((int) $order['shipping_paise']) ?></td></tr>
    <tr><td>Tax</td><td class="text-right"><?= money((int) $order['tax_paise']) ?></td></tr>
    <tr class="grand"><td>Total</td><td class="text-right"><?= money((int) $order['total_paise']) ?></td></tr>
  </table>

  <p class="muted" style="margin-top:40px;">Thank you for shopping with Happy Tails Pet Store.</p>
</body>
</html>
