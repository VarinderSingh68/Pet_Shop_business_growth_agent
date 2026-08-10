<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $appointments */
/** @var string $date */
/** @var string|null $staffId */
/** @var array $allStaff */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/services/staff" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Staff</a>
  <a href="/admin/services/services" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Services</a>
  <a href="/admin/services/appointments" class="text-sm font-semibold border-b-2 border-ink pb-1">Appointments</a>
</div>

<form method="GET" action="/admin/services/appointments" class="flex flex-wrap gap-2 mb-4">
  <a href="?date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>&staff_id=<?= e((string) $staffId) ?>" class="btn btn-secondary btn-sm">&larr; Prev day</a>
  <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()" class="input text-sm">
  <a href="?date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>&staff_id=<?= e((string) $staffId) ?>" class="btn btn-secondary btn-sm">Next day &rarr;</a>
  <select name="staff_id" onchange="this.form.submit()" class="input text-sm">
    <option value="">All staff</option>
    <?php foreach ($allStaff as $s): ?>
      <option value="<?= (int) $s['id'] ?>" <?= (string) $staffId === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="space-y-3">
  <?php foreach ($appointments as $a): ?>
    <div class="card-tag p-4 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <p class="font-semibold text-sm">
          <?= date('g:i A', strtotime($a['start_at'])) ?> — <?= e($a['service_name']) ?>
          <span class="text-ink/50 font-normal">with <?= e($a['staff_name']) ?></span>
        </p>
        <p class="text-xs text-ink/50 mt-1">
          <?= e($a['user_name'] ?? $a['guest_name'] ?? 'Guest') ?><?= $a['pet_name'] ? ' · ' . e($a['pet_name']) : '' ?>
          · <?= e($a['booking_number']) ?>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <span class="badge badge-info capitalize"><?= e(str_replace('_', ' ', $a['status'])) ?></span>
        <form method="POST" action="/admin/services/appointments/<?= (int) $a['id'] ?>/status" class="flex items-center gap-1">
          <?= csrf_field() ?>
          <select name="status" class="text-xs input">
            <?php foreach (['booked', 'confirmed', 'completed', 'cancelled', 'no_show'] as $st): ?>
              <option value="<?= $st ?>" <?= $a['status'] === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-secondary btn-sm">Update</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if ($appointments === []): ?>
    <div class="card-tag p-10 text-center bg-white">
      <p class="text-ink/50">Nothing booked on <?= e(date('d M Y', strtotime($date))) ?><?= $staffId ? ' for this staff member' : '' ?>.</p>
    </div>
  <?php endif; ?>
</div>

<?php \App\Core\View::stop(); ?>
