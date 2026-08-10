<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array|null $staff */
/** @var array $blackoutDates */

$isEdit = $staff !== null;
$hours = $isEdit && $staff['working_hours'] ? json_decode($staff['working_hours'], true) : [];
$days = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];
?>

<a href="/admin/services/staff" class="text-sm text-ink/50 hover:text-leash">&larr; Staff</a>

<div class="mt-3 grid lg:grid-cols-[1fr_360px] gap-6">
  <form method="POST" action="<?= $isEdit ? '/admin/services/staff/' . (int) $staff['id'] : '/admin/services/staff' ?>" class="card-tag p-5 bg-white space-y-4">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label for="name" class="block text-sm font-semibold mb-1">Name</label>
        <input id="name" type="text" name="name" required value="<?= e($staff['name'] ?? '') ?>" class="input">
      </div>
      <div>
        <label for="title" class="block text-sm font-semibold mb-1">Title</label>
        <input id="title" type="text" name="title" placeholder="e.g. Senior Groomer" value="<?= e($staff['title'] ?? '') ?>" class="input">
      </div>
    </div>
    <div>
      <label for="bio" class="block text-sm font-semibold mb-1">Bio</label>
      <textarea id="bio" name="bio" rows="3" class="input"><?= e($staff['bio'] ?? '') ?></textarea>
    </div>

    <div>
      <p class="text-sm font-semibold mb-2">Working hours</p>
      <div class="space-y-2">
        <?php foreach ($days as $key => $label): ?>
          <div class="flex items-center gap-3">
            <span class="w-24 text-sm"><?= $label ?></span>
            <input type="time" name="hours_<?= $key ?>_start" value="<?= e($hours[$key]['start'] ?? '') ?>" class="input text-sm">
            <span class="text-ink/40">to</span>
            <input type="time" name="hours_<?= $key ?>_end" value="<?= e($hours[$key]['end'] ?? '') ?>" class="input text-sm">
          </div>
        <?php endforeach; ?>
      </div>
      <p class="text-xs text-ink/50 mt-1">Leave both fields blank for a day off.</p>
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" value="1" <?= ($staff['is_active'] ?? 1) ? 'checked' : '' ?> class="border-2 border-ink">
      Active — bookable for new appointments
    </label>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Add staff member' ?></button>
  </form>

  <?php if ($isEdit): ?>
    <div class="card-tag p-5 bg-white h-fit">
      <p class="font-display font-semibold">Blackout dates</p>
      <p class="text-xs text-ink/50 mt-1">Days this staff member is unavailable — holidays, leave, etc.</p>
      <ul class="mt-3 space-y-2 text-sm">
        <?php foreach ($blackoutDates as $b): ?>
          <li class="flex items-center justify-between border-b border-mist pb-2">
            <span><?= e($b['date']) ?><?= $b['reason'] ? ' — ' . e($b['reason']) : '' ?></span>
            <form method="POST" action="/admin/services/staff/<?= (int) $staff['id'] ?>/blackout/<?= (int) $b['id'] ?>/delete">
              <?= csrf_field() ?>
              <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-leash hover:underline"><?= icon('x', 'h-3 w-3') ?> Remove</button>
            </form>
          </li>
        <?php endforeach; ?>
        <?php if ($blackoutDates === []): ?><li class="text-ink/50">None set.</li><?php endif; ?>
      </ul>
      <form method="POST" action="/admin/services/staff/<?= (int) $staff['id'] ?>/blackout" class="mt-4 space-y-2">
        <?= csrf_field() ?>
        <input type="date" name="date" required class="input text-sm">
        <input type="text" name="reason" placeholder="Reason (optional)" class="input text-sm">
        <button type="submit" class="w-full btn btn-secondary btn-sm">Add blackout date</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php \App\Core\View::stop(); ?>
