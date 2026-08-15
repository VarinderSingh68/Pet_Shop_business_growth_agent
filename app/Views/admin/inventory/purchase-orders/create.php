<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $suppliers */
?>

<a href="/admin/inventory/purchase-orders" class="text-sm text-ink/50 hover:text-leash">&larr; Purchase orders</a>

<div class="mt-4 max-w-lg card-tag p-6 bg-white">
  <p class="font-display text-lg font-semibold mb-4">New purchase order</p>
  <?php if ($suppliers === []): ?>
    <p class="text-sm text-ink/60">You need at least one active supplier first. <a href="/admin/inventory/suppliers" class="text-leash font-semibold hover:underline">Add one &rarr;</a></p>
  <?php else: ?>
    <form method="POST" action="/admin/inventory/purchase-orders" class="space-y-3">
      <?= csrf_field() ?>
      <div>
        <label for="supplier_id" class="block text-xs font-semibold mb-1">Supplier</label>
        <select id="supplier_id" name="supplier_id" required class="input text-sm w-full">
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="expected_at" class="block text-xs font-semibold mb-1">Expected delivery (optional)</label>
        <input id="expected_at" type="date" name="expected_at" class="input text-sm w-full">
      </div>
      <div>
        <label for="notes" class="block text-xs font-semibold mb-1">Notes</label>
        <textarea id="notes" name="notes" rows="3" class="input text-sm w-full"></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Create — add line items next</button>
    </form>
  <?php endif; ?>
</div>

<?php \App\Core\View::stop(); ?>
