<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $addresses */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12" x-data="{ showForm: <?= $addresses === [] ? 'true' : 'false' ?> }">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'addresses']); ?>

    <div>
      <div class="flex items-center justify-between">
        <h1 class="font-display text-3xl font-bold">My addresses</h1>
        <button type="button" @click="showForm = !showForm" class="btn btn-secondary btn-sm">
          <span x-text="showForm ? 'Cancel' : 'Add address'"></span>
        </button>
      </div>

      <form method="POST" action="/account/addresses" x-show="showForm" x-cloak class="mt-6 card-tag p-6 grid sm:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <div>
          <label for="addr_label" class="block text-sm font-semibold mb-1">Label</label>
          <input id="addr_label" type="text" name="label" placeholder="Home, Work..." class="input">
        </div>
        <div>
          <label for="addr_full_name" class="block text-sm font-semibold mb-1">Full name</label>
          <input id="addr_full_name" type="text" name="full_name" required class="input">
        </div>
        <div>
          <label for="addr_phone" class="block text-sm font-semibold mb-1">Phone</label>
          <input id="addr_phone" type="tel" name="phone" required class="input">
        </div>
        <div>
          <label for="addr_postal" class="block text-sm font-semibold mb-1">Postal code</label>
          <input id="addr_postal" type="text" name="postal_code" required class="input">
        </div>
        <div class="sm:col-span-2">
          <label for="addr_line1" class="block text-sm font-semibold mb-1">Address line 1</label>
          <input id="addr_line1" type="text" name="line1" required class="input">
        </div>
        <div class="sm:col-span-2">
          <label for="addr_line2" class="block text-sm font-semibold mb-1">Address line 2 <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="addr_line2" type="text" name="line2" class="input">
        </div>
        <div>
          <label for="addr_city" class="block text-sm font-semibold mb-1">City</label>
          <input id="addr_city" type="text" name="city" required class="input">
        </div>
        <div>
          <label for="addr_state" class="block text-sm font-semibold mb-1">State</label>
          <input id="addr_state" type="text" name="state" required class="input">
        </div>
        <div class="sm:col-span-2 flex items-center gap-2">
          <input type="checkbox" id="addr_default" name="is_default" value="1" class="border-2 border-ink">
          <label for="addr_default" class="text-sm">Set as default address</label>
        </div>
        <div class="sm:col-span-2">
          <button type="submit" class="btn btn-primary">Save address</button>
        </div>
      </form>

      <?php if ($addresses !== []): ?>
        <div class="mt-8 grid sm:grid-cols-2 gap-5">
          <?php foreach ($addresses as $addr): ?>
            <div class="card-tag p-5">
              <?php if ($addr['is_default']): ?><div class="card-tag__tab">Default</div><?php endif; ?>
              <p class="font-semibold <?= $addr['is_default'] ? 'mt-6' : '' ?>"><?= e($addr['label']) ?></p>
              <p class="text-sm text-ink/70 mt-1"><?= e($addr['full_name']) ?><br>
                <?= e($addr['line1']) ?><?= $addr['line2'] ? ', ' . e($addr['line2']) : '' ?><br>
                <?= e($addr['city']) ?>, <?= e($addr['state']) ?> <?= e($addr['postal_code']) ?><br>
                <?= e($addr['phone']) ?></p>
              <form method="POST" action="/account/addresses/<?= (int) $addr['id'] ?>/delete" class="mt-3">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs font-semibold text-ink/50 hover:text-leash">Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
