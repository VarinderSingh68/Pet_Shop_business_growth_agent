<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $orders */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var string $status */
/** @var string $search */
/** @var array $statuses */

$totalPages = max(1, (int) ceil($total / $perPage));
?>

<form method="GET" action="/admin/orders" class="flex flex-wrap gap-2 mb-4" x-data>
  <div class="relative">
    <label for="order-search" class="sr-only">Search orders</label>
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink/40"><?= icon('search', 'h-4 w-4') ?></span>
    <input id="order-search" type="text" name="q" value="<?= e($search) ?>" placeholder="Order #, email, or name" class="input pl-9 !w-auto min-w-[220px]"
           x-on:input.debounce.500ms="$el.form.requestSubmit()"
           x-init="if ($el.value) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }">
  </div>
  <label for="order-status" class="sr-only">Filter by status</label>
  <select id="order-status" name="status" class="input !w-auto">
    <option value="">All statuses</option>
    <?php foreach ($statuses as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-secondary"><?= icon('filter', 'h-4 w-4') ?> Filter</button>
</form>

<?php
  $statusBadge = ['pending_payment' => 'badge-warning', 'confirmed' => 'badge-info', 'processing' => 'badge-info', 'shipped' => 'badge-info', 'delivered' => 'badge-success', 'cancelled' => 'badge-danger', 'refunded' => 'badge-neutral'];
  $paymentBadge = ['pending' => 'badge-warning', 'paid' => 'badge-success', 'failed' => 'badge-danger', 'refunded' => 'badge-neutral', 'partially_refunded' => 'badge-neutral'];
?>

<form method="POST" action="/admin/orders/bulk">
  <?= csrf_field() ?>
  <div class="flex items-center gap-2 mb-2">
    <label for="bulk-status" class="sr-only">Bulk set status</label>
    <select id="bulk-status" name="bulk_status" class="input !w-auto py-1.5">
      <option value="">Bulk set status…</option>
      <?php foreach ($statuses as $s): ?>
        <option value="<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
  </div>

  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="border-b-2 border-ink">
          <th class="p-2 w-8"><label class="sr-only" for="order-select-all">Select all</label><input id="order-select-all" type="checkbox" onclick="document.querySelectorAll('.order-row-check').forEach(c=>c.checked=this.checked)"></th>
          <th class="p-2 text-left">Order</th><th class="p-2 text-left">Customer</th><th class="p-2 text-left">Placed</th>
          <th class="p-2 text-right">Total</th><th class="p-2 text-left">Payment</th><th class="p-2 text-left">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr class="border-b border-mist cursor-pointer hover:bg-tennis/10 transition-colors duration-150" onclick="window.location='/admin/orders/<?= (int) $o['id'] ?>'">
            <td class="p-2" onclick="event.stopPropagation()"><label class="sr-only" for="order-check-<?= (int) $o['id'] ?>">Select order <?= e($o['order_number']) ?></label><input id="order-check-<?= (int) $o['id'] ?>" class="order-row-check" type="checkbox" name="ids[]" value="<?= (int) $o['id'] ?>"></td>
            <td class="p-2 font-medium"><?= e($o['order_number']) ?></td>
            <td class="p-2"><?= e($o['customer_name'] ?? $o['guest_email'] ?? 'Guest') ?></td>
            <td class="p-2 text-ink/60"><?= e(date('d M Y', strtotime((string) $o['created_at']))) ?></td>
            <td class="p-2 text-right font-semibold"><?= money((int) $o['total_paise']) ?></td>
            <td class="p-2"><span class="badge <?= $paymentBadge[$o['payment_status']] ?? 'badge-neutral' ?>"><?= e(str_replace('_', ' ', $o['payment_status'])) ?></span></td>
            <td class="p-2"><span class="badge <?= $statusBadge[$o['status']] ?? 'badge-neutral' ?>"><?= e(str_replace('_', ' ', $o['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if ($orders === []): ?>
          <tr><td colspan="7" class="p-6 text-center text-ink/50">No orders match.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if ($totalPages > 1): ?>
  <nav class="mt-4 flex gap-2" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
         class="w-8 h-8 flex items-center justify-center border-2 text-sm transition-colors duration-150 <?= $i === $page ? 'border-leash bg-leash text-paper' : 'border-ink hover:border-leash' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php \App\Core\View::stop(); ?>
