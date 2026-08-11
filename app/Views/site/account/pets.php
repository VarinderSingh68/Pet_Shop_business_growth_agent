<?php
\App\Core\View::extend('layouts/site');
\App\Core\View::start('content');

/** @var array $pets */
?>

<section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12" x-data="{ showForm: <?= $pets === [] ? 'true' : 'false' ?> }">
  <div class="grid lg:grid-cols-[200px_1fr] gap-10">
    <?php \App\Core\View::include('components/account-nav', ['active' => 'pets']); ?>

    <div>
      <div class="flex items-center justify-between">
        <h1 class="font-display text-3xl font-bold">My pets</h1>
        <button type="button" @click="showForm = !showForm" class="btn btn-secondary btn-sm">
          <span x-text="showForm ? 'Cancel' : 'Add a pet'"></span>
        </button>
      </div>

      <p class="mt-2 text-ink/60 text-sm">Pet profiles power portion suggestions, reorder timing, and birthday offers.</p>

      <form method="POST" action="/account/pets" x-show="showForm" x-cloak class="mt-6 card-tag p-6 grid sm:grid-cols-2 gap-4">
        <?= csrf_field() ?>
        <div>
          <label for="pet_name" class="block text-sm font-semibold mb-1">Name</label>
          <input id="pet_name" type="text" name="name" required class="input">
        </div>
        <div>
          <label for="pet_species" class="block text-sm font-semibold mb-1">Species</label>
          <select id="pet_species" name="species" required class="input">
            <option value="dog">Dog</option>
            <option value="cat">Cat</option>
            <option value="bird">Bird</option>
            <option value="fish">Fish</option>
            <option value="small_pet">Small pet</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label for="pet_breed" class="block text-sm font-semibold mb-1">Breed <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="pet_breed" type="text" name="breed" class="input">
        </div>
        <div>
          <label for="pet_sex" class="block text-sm font-semibold mb-1">Sex</label>
          <select id="pet_sex" name="sex" class="input">
            <option value="unknown">Prefer not to say</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>
        <div>
          <label for="pet_birthday" class="block text-sm font-semibold mb-1">Birthday <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="pet_birthday" type="date" name="birthday" max="<?= date('Y-m-d') ?>" class="input">
        </div>
        <div>
          <label for="pet_weight" class="block text-sm font-semibold mb-1">Weight, kg <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="pet_weight" type="number" step="0.1" min="0" name="weight_kg" class="input">
        </div>
        <div class="sm:col-span-2">
          <label for="pet_allergies" class="block text-sm font-semibold mb-1">Allergies <span class="font-normal text-ink/50">(optional)</span></label>
          <input id="pet_allergies" type="text" name="allergies" placeholder="e.g. chicken, grain" class="input">
        </div>
        <div class="sm:col-span-2">
          <button type="submit" class="btn btn-primary">Save pet</button>
        </div>
      </form>

      <?php if ($pets !== []): ?>
        <div class="mt-8 grid sm:grid-cols-2 gap-5">
          <?php foreach ($pets as $pet): ?>
            <div class="card-tag p-5">
              <div class="card-tag__tab"><?= e(ucfirst($pet['species'])) ?></div>
              <p class="font-display text-lg font-semibold mt-6"><?= e($pet['name']) ?></p>
              <p class="text-sm text-ink/60 mt-1">
                <?= e($pet['breed'] ?? 'Breed not set') ?>
                <?php $age = \App\Models\Pet::ageLabel($pet['birthday']); ?>
                <?php if ($age): ?> &middot; <?= e($age) ?><?php endif; ?>
                <?php if ($pet['weight_grams']): ?> &middot; <?= number_format($pet['weight_grams'] / 1000, 1) ?>kg<?php endif; ?>
              </p>
              <?php if ($pet['allergies']): ?>
                <p class="text-xs mt-2 text-leash font-medium">Allergic to: <?= e($pet['allergies']) ?></p>
              <?php endif; ?>
              <form method="POST" action="/account/pets/<?= (int) $pet['id'] ?>/delete" class="mt-3">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs font-semibold text-ink/50 hover:text-leash">Remove profile</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php \App\Core\View::stop(); ?>
