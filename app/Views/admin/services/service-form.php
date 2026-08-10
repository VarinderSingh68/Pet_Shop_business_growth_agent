<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array|null $service */
/** @var array $allStaff */
/** @var array $assignedStaffIds */

$isEdit = $service !== null;
?>

<a href="/admin/services/services" class="text-sm text-ink/50 hover:text-leash">&larr; Services</a>

<form method="POST" action="<?= $isEdit ? '/admin/services/services/' . (int) $service['id'] : '/admin/services/services' ?>" class="mt-3 max-w-2xl card-tag p-5 bg-white space-y-4">
  <?= csrf_field() ?>
  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label for="name" class="block text-sm font-semibold mb-1">Name</label>
      <input id="name" type="text" name="name" required value="<?= e($service['name'] ?? '') ?>" class="input">
    </div>
    <div>
      <label for="category" class="block text-sm font-semibold mb-1">Category</label>
      <select id="category" name="category" required class="input">
        <?php foreach (['grooming', 'boarding', 'vet', 'training'] as $cat): ?>
          <option value="<?= $cat ?>" <?= ($service['category'] ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="duration_minutes" class="block text-sm font-semibold mb-1">Duration (minutes)</label>
      <input id="duration_minutes" type="number" name="duration_minutes" required value="<?= e($service['duration_minutes'] ?? 60) ?>" class="input">
    </div>
    <div>
      <label for="price" class="block text-sm font-semibold mb-1">Price (₹)</label>
      <input id="price" type="number" step="0.01" name="price" required value="<?= $service ? number_format($service['price_paise'] / 100, 2, '.', '') : '' ?>" class="input">
    </div>
    <div>
      <label for="deposit" class="block text-sm font-semibold mb-1">Deposit (₹) <span class="font-normal text-ink/50">(optional)</span></label>
      <input id="deposit" type="number" step="0.01" name="deposit" value="<?= $service && $service['deposit_paise'] ? number_format($service['deposit_paise'] / 100, 2, '.', '') : '' ?>" class="input">
    </div>
    <div>
      <label for="reschedule_cutoff_hours" class="block text-sm font-semibold mb-1">Reschedule cutoff (hours)</label>
      <input id="reschedule_cutoff_hours" type="number" name="reschedule_cutoff_hours" value="<?= e($service['reschedule_cutoff_hours'] ?? 24) ?>" class="input">
    </div>
  </div>
  <div>
    <label for="description" class="block text-sm font-semibold mb-1">Description</label>
    <textarea id="description" name="description" rows="3" class="input"><?= e($service['description'] ?? '') ?></textarea>
  </div>

  <div>
    <p class="text-sm font-semibold mb-2">Staff who provide this service</p>
    <div class="flex flex-wrap gap-3">
      <?php foreach ($allStaff as $s): ?>
        <label class="flex items-center gap-2 cursor-pointer border-2 border-ink px-3 py-1.5 text-sm has-[:checked]:border-leash has-[:checked]:bg-leash/5">
          <input type="checkbox" name="staff_ids[]" value="<?= (int) $s['id'] ?>" <?= in_array($s['id'], $assignedStaffIds, true) ? 'checked' : '' ?>>
          <?= e($s['name']) ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" <?= ($service['is_active'] ?? 1) ? 'checked' : '' ?> class="border-2 border-ink">
    Active — bookable on the storefront
  </label>

  <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create service' ?></button>
</form>

<?php \App\Core\View::stop(); ?>
