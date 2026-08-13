<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $values */
?>

<form method="POST" action="/admin/settings" class="max-w-3xl space-y-6">
  <?= csrf_field() ?>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Store details</p>
    <div class="mt-3 grid sm:grid-cols-2 gap-4">
      <div>
        <label for="store_name" class="block text-sm font-semibold mb-1">Store name</label>
        <input id="store_name" type="text" name="store_name" value="<?= e($values['store_name']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="store_phone" class="block text-sm font-semibold mb-1">Phone</label>
        <input id="store_phone" type="text" name="store_phone" value="<?= e($values['store_phone']) ?>" class="input text-sm">
      </div>
      <div class="sm:col-span-2">
        <label for="store_address" class="block text-sm font-semibold mb-1">Address</label>
        <input id="store_address" type="text" name="store_address" value="<?= e($values['store_address']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="store_email" class="block text-sm font-semibold mb-1">Contact email</label>
        <input id="store_email" type="email" name="store_email" value="<?= e($values['store_email']) ?>" class="input text-sm">
      </div>
    </div>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Commerce</p>
    <div class="mt-3 grid sm:grid-cols-3 gap-4">
      <div>
        <label for="tax_rate_percent" class="block text-sm font-semibold mb-1">Tax rate (%)</label>
        <input id="tax_rate_percent" type="number" step="0.1" name="tax_rate_percent" value="<?= e($values['tax_rate_percent']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="shipping_flat_rate" class="block text-sm font-semibold mb-1">Flat shipping (₹)</label>
        <input id="shipping_flat_rate" type="number" step="0.01" name="shipping_flat_rate" value="<?= e($values['shipping_flat_rate']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="shipping_free_threshold" class="block text-sm font-semibold mb-1">Free shipping over (₹)</label>
        <input id="shipping_free_threshold" type="number" step="0.01" name="shipping_free_threshold" value="<?= e($values['shipping_free_threshold']) ?>" class="input text-sm">
      </div>
    </div>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">SEO defaults</p>
    <div class="mt-3 space-y-3">
      <div>
        <label for="seo_default_title" class="block text-sm font-semibold mb-1">Default meta title</label>
        <input id="seo_default_title" type="text" name="seo_default_title" value="<?= e($values['seo_default_title']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="seo_default_description" class="block text-sm font-semibold mb-1">Default meta description</label>
        <textarea id="seo_default_description" name="seo_default_description" rows="2" class="input text-sm"><?= e($values['seo_default_description']) ?></textarea>
      </div>
    </div>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Social links</p>
    <div class="mt-3 grid sm:grid-cols-2 gap-4">
      <div>
        <label for="social_instagram" class="block text-sm font-semibold mb-1">Instagram URL</label>
        <input id="social_instagram" type="url" name="social_instagram" value="<?= e($values['social_instagram']) ?>" class="input text-sm">
      </div>
      <div>
        <label for="social_facebook" class="block text-sm font-semibold mb-1">Facebook URL</label>
        <input id="social_facebook" type="url" name="social_facebook" value="<?= e($values['social_facebook']) ?>" class="input text-sm">
      </div>
    </div>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">Theme</p>
    <p class="text-xs text-ink/50 mt-1">Pick the storefront's primary accent from the design system's curated set — keeps contrast and the "one loud color" rule intact.</p>
    <div class="mt-3 flex gap-3">
      <?php foreach (['leash' => 'Leash Red', 'fern' => 'Fern Green', 'tennis' => 'Tennis Gold'] as $key => $label): ?>
        <label class="flex items-center gap-2 cursor-pointer border-2 border-ink px-3 py-1.5 text-sm has-[:checked]:border-leash">
          <input type="radio" name="theme_accent" value="<?= $key ?>" <?= $values['theme_accent'] === $key ? 'checked' : '' ?>>
          <?= $label ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card-tag p-5 bg-white">
    <p class="font-display font-semibold">System</p>
    <label class="flex items-center gap-2 text-sm mt-2">
      <input type="checkbox" name="maintenance_mode" value="1" <?= $values['maintenance_mode'] === '1' ? 'checked' : '' ?> class="border-2 border-ink">
      Maintenance mode — only owner/developer accounts can browse the storefront
    </label>
  </div>

  <button type="submit" class="btn btn-primary">Save settings</button>
</form>

<div class="max-w-3xl mt-6 grid sm:grid-cols-3 gap-4">
  <a href="/admin/roles" class="card-tag card-tag--pop p-5 bg-white block">
    <span class="icon-chip icon-chip-plum"><?= icon('shield-check', 'h-4 w-4') ?></span>
    <p class="font-display font-semibold mt-3">Roles &amp; permissions</p>
    <p class="text-xs text-ink/50 mt-1">Choose what each staff role can access.</p>
  </a>
  <a href="/admin/activity" class="card-tag card-tag--pop p-5 bg-white block">
    <span class="icon-chip icon-chip-info"><?= icon('clock', 'h-4 w-4') ?></span>
    <p class="font-display font-semibold mt-3">Activity log</p>
    <p class="text-xs text-ink/50 mt-1">See what staff have changed, and when.</p>
  </a>
  <a href="/admin/security" class="card-tag card-tag--pop p-5 bg-white block">
    <span class="icon-chip icon-chip-fern"><?= icon('shield-check', 'h-4 w-4') ?></span>
    <p class="font-display font-semibold mt-3">Your security</p>
    <p class="text-xs text-ink/50 mt-1">Set up two-factor authentication.</p>
  </a>
</div>

<?php \App\Core\View::stop(); ?>
