<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $enquiry */

$statusColors = ['open' => 'badge-danger', 'in_progress' => 'badge-info', 'resolved' => 'badge-success'];
?>

<a href="/admin/support" class="text-sm text-ink/50 hover:text-ink">&larr; All enquiries</a>

<div class="mt-4 grid lg:grid-cols-[1fr_280px] gap-6">
  <div class="space-y-4">
    <div class="border-2 border-ink bg-white p-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="font-display text-lg font-bold"><?= e($enquiry['subject']) ?></h2>
          <p class="text-sm text-ink/50 mt-1"><?= e($enquiry['name']) ?> &lt;<?= e($enquiry['email']) ?>&gt;<?= $enquiry['phone'] ? ' · ' . e($enquiry['phone']) : '' ?></p>
          <?php if ($enquiry['order_number']): ?><p class="text-sm text-ink/50">Order: <span class="font-mono"><?= e($enquiry['order_number']) ?></span></p><?php endif; ?>
        </div>
        <span class="badge <?= $statusColors[$enquiry['status']] ?? 'badge-info' ?> capitalize"><?= e(str_replace('_', ' ', $enquiry['status'])) ?></span>
      </div>
      <p class="mt-4 text-sm text-ink/50"><?= e(date('d M Y, g:i A', strtotime((string) $enquiry['created_at']))) ?></p>
      <div class="mt-4 pt-4 border-t border-mist whitespace-pre-line text-sm"><?= e($enquiry['message']) ?></div>
    </div>

    <?php if ($enquiry['staff_reply']): ?>
      <div class="border-2 border-fern bg-fern/5 p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-fern">Staff reply &middot; <?= e(date('d M Y, g:i A', strtotime((string) $enquiry['replied_at']))) ?></p>
        <div class="mt-2 whitespace-pre-line text-sm"><?= e($enquiry['staff_reply']) ?></div>
      </div>
    <?php endif; ?>

    <div class="border-2 border-ink bg-white p-5">
      <p class="font-display font-semibold mb-3">Reply by email</p>
      <form method="POST" action="/admin/support/<?= (int) $enquiry['id'] ?>/reply">
        <?= csrf_field() ?>
        <textarea name="staff_reply" rows="5" required placeholder="Write a reply — this is emailed to the customer and marks the enquiry resolved." class="input text-sm w-full"><?= e($enquiry['staff_reply'] ?? '') ?></textarea>
        <button type="submit" class="btn btn-primary btn-sm mt-3">Send reply</button>
      </form>
    </div>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold mb-3">Status</p>
    <form method="POST" action="/admin/support/<?= (int) $enquiry['id'] ?>/status" class="space-y-2">
      <?= csrf_field() ?>
      <select name="status" class="input text-sm w-full">
        <?php foreach (['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $key => $label): ?>
          <option value="<?= $key ?>" <?= $enquiry['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Update status</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
