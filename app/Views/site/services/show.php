<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $service */
/** @var array $staff */
/** @var int $selectedStaffId */
/** @var array $slotsByDay */
/** @var array $pets */
?>

<section class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-14" x-data="{ selectedSlot: null }">
  <a href="/services" class="text-sm text-ink/50 hover:text-leash">&larr; All services</a>
  <h1 class="font-display text-3xl font-bold mt-3"><?= e($service['name']) ?></h1>
  <p class="mt-2 text-ink/70 max-w-2xl"><?= e($service['description']) ?></p>
  <div class="mt-3 flex items-center gap-4 text-sm">
    <span><?= (int) $service['duration_minutes'] ?> minutes</span>
    <span class="font-display font-bold text-lg"><?= money((int) $service['price_paise']) ?></span>
    <?php if ($service['deposit_paise']): ?>
      <span class="text-ink/50"><?= money((int) $service['deposit_paise']) ?> deposit required</span>
    <?php endif; ?>
  </div>

  <?php if ($staff === []): ?>
    <p class="mt-8 text-ink/60">No staff are currently available for this service. Please check back soon.</p>
  <?php else: ?>
    <form method="GET" action="/services/<?= e($service['slug']) ?>" class="mt-8">
      <label for="staff" class="block text-sm font-semibold mb-2">Choose a staff member</label>
      <select id="staff" name="staff" onchange="this.form.submit()" class="input">
        <?php foreach ($staff as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= (int) $s['id'] === $selectedStaffId ? 'selected' : '' ?>>
            <?= e($s['name']) ?><?= $s['title'] ? ' — ' . e($s['title']) : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($slotsByDay === []): ?>
      <p class="mt-8 text-ink/60">No open slots in the next three weeks for this staff member.</p>
    <?php else: ?>
      <div class="mt-8 space-y-6">
        <?php foreach ($slotsByDay as $day => $slots): ?>
          <div>
            <p class="font-semibold text-sm"><?= date('l, d M', strtotime($day)) ?></p>
            <div class="mt-2 flex flex-wrap gap-2">
              <?php foreach ($slots as $slot): ?>
                <button type="button" @click="selectedSlot = <?= (int) $slot['id'] ?>"
                        :class="selectedSlot === <?= (int) $slot['id'] ?> ? 'border-leash bg-leash text-paper' : 'border-ink hover:border-leash'"
                        class="border-2 px-3 py-2 text-sm font-medium">
                  <?= date('g:i A', strtotime($slot['start_at'])) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <form method="POST" action="/services/book" class="mt-10 card-tag p-6 max-w-lg" x-show="selectedSlot" x-cloak>
        <?= csrf_field() ?>
        <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
        <input type="hidden" name="staff_id" value="<?= $selectedStaffId ?>">
        <input type="hidden" name="slot_id" x-bind:value="selectedSlot">

        <?php if (auth()->check()): ?>
          <?php if ($pets !== []): ?>
            <div class="mb-4">
              <label for="pet_id" class="block text-sm font-semibold mb-1">Which pet? <span class="font-normal text-ink/50">(optional)</span></label>
              <select id="pet_id" name="pet_id" class="input">
                <option value="">Not specified</option>
                <?php foreach ($pets as $pet): ?>
                  <option value="<?= (int) $pet['id'] ?>"><?= e($pet['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="mb-4">
            <label for="guest_name" class="block text-sm font-semibold mb-1">Full name</label>
            <input id="guest_name" type="text" name="guest_name" required class="input">
          </div>
          <div class="mb-4 grid grid-cols-2 gap-3">
            <div>
              <label for="guest_email" class="block text-sm font-semibold mb-1">Email</label>
              <input id="guest_email" type="email" name="guest_email" required class="input">
            </div>
            <div>
              <label for="guest_phone" class="block text-sm font-semibold mb-1">Phone</label>
              <input id="guest_phone" type="tel" name="guest_phone" required class="input">
            </div>
          </div>
        <?php endif; ?>

        <div class="mb-4">
          <label for="notes" class="block text-sm font-semibold mb-1">Notes <span class="font-normal text-ink/50">(optional)</span></label>
          <textarea id="notes" name="notes" rows="2" class="input"></textarea>
        </div>

        <button type="submit" class="w-full btn btn-primary">
          Confirm booking
        </button>
      </form>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php \App\Core\View::stop(); ?>
