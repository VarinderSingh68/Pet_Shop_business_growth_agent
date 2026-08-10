<?php
/** @var string $from */
/** @var string $to */
/** @var array $summary */
/** @var array $byDay */
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: sans-serif; font-size: 12px; color: #12141c; }
  h1 { font-size: 20px; margin-bottom: 0; }
  p.sub { color: #666; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
  th { background: #f6f7f2; }
  td.num, th.num { text-align: right; }
  .summary { margin-top: 16px; }
  .summary td { border: none; padding: 2px 8px 2px 0; }
</style>
</head>
<body>
  <h1>Happy Tails Pet Store — Sales Report</h1>
  <p class="sub"><?= e($from) ?> to <?= e($to) ?> &middot; generated <?= e(now()) ?></p>

  <table class="summary">
    <tr><td><strong>Orders</strong></td><td><?= $summary['order_count'] ?></td></tr>
    <tr><td><strong>Subtotal</strong></td><td><?= money($summary['subtotal_paise']) ?></td></tr>
    <tr><td><strong>Tax collected</strong></td><td><?= money($summary['tax_paise']) ?></td></tr>
    <tr><td><strong>Discounts given</strong></td><td><?= money($summary['discount_paise']) ?></td></tr>
    <tr><td><strong>Total revenue</strong></td><td><?= money($summary['total_paise']) ?></td></tr>
  </table>

  <table>
    <thead><tr><th>Date</th><th class="num">Orders</th><th class="num">Revenue</th></tr></thead>
    <tbody>
      <?php foreach ($byDay as $d): ?>
        <tr><td><?= e($d['date']) ?></td><td class="num"><?= (int) $d['order_count'] ?></td><td class="num"><?= money((int) $d['revenue_paise']) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
