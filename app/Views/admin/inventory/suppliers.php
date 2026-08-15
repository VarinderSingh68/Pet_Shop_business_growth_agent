<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $suppliers */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/inventory" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Stock</a>
  <a href="/admin/inventory/suppliers" class="text-sm font-semibold border-b-2 border-ink pb-1">Suppliers</a>
  <a href="/admin/inventory/purchase-orders" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Purchase orders</a>
</div>

<div class="grid lg:grid-cols-[1fr_320px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead><tr class="border-b-2 border-ink"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Contact</th><th class="p-2 text-left">Email</th><th class="p-2 text-left">Phone</th><th class="p-2 text-left">Active</th><th class="p-2"></th></tr></thead>
      <tbody>
        <?php foreach ($suppliers as $s): ?>
          <tr class="border-b border-mist">
            <form method="POST" action="/admin/inventory/suppliers/<?= (int) $s['id'] ?>">
              <?= csrf_field() ?>
              <td class="p-2"><input type="text" name="name" value="<?= e($s['name']) ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2"><input type="text" name="contact_name" value="<?= e($s['contact_name'] ?? '') ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2"><input type="email" name="email" value="<?= e($s['email'] ?? '') ?>" class="border border-ink px-1.5 py-1 w-full"></td>
              <td class="p-2"><input type="text" name="phone" value="<?= e($s['phone'] ?? '') ?>" class="border border-ink px-1.5 py-1 w-32"></td>
              <td class="p-2"><input type="checkbox" name="is_active" value="1" <?= $s['is_active'] ? 'checked' : '' ?>></td>
              <td class="p-2 whitespace-nowrap">
                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-fern hover:underline"><?= icon('check', 'h-3 w-3') ?> Save</button>
                <button type="submit" formaction="/admin/inventory/suppliers/<?= (int) $s['id'] ?>/delete" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline ml-2"><?= icon('trash', 'h-3 w-3') ?> Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        <?php if ($suppliers === []): ?><tr><td colspan="6" class="p-6 text-center text-ink/50">No suppliers yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">Add supplier</p>
    <form method="POST" action="/admin/inventory/suppliers" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Name" required class="input text-sm w-full">
      <input type="text" name="contact_name" placeholder="Contact person" class="input text-sm w-full">
      <input type="email" name="email" placeholder="Email" class="input text-sm w-full">
      <input type="text" name="phone" placeholder="Phone" class="input text-sm w-full">
      <textarea name="address" rows="2" placeholder="Address" class="input text-sm w-full"></textarea>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Add</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
