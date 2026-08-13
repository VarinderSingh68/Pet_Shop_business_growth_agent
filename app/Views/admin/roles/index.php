<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $roles */
/** @var array $permissionGroups */
/** @var array $grantsByRole */
/** @var array $lockedRoleSlugs */
?>

<div class="space-y-6">
  <?php foreach ($roles as $role): ?>
    <?php $isLocked = in_array($role['slug'], $lockedRoleSlugs, true); ?>
    <div class="card-tag p-5 bg-white">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <p class="font-display font-semibold"><?= e($role['name']) ?></p>
          <p class="text-sm text-ink/60"><?= e($role['description'] ?? '') ?></p>
        </div>
        <?php if ($isLocked): ?>
          <span class="badge badge-info">Full access — not editable here</span>
        <?php endif; ?>
      </div>

      <?php if ($isLocked): ?>
        <p class="mt-4 text-sm text-ink/50">This role always has every permission by design, so its grants can't be changed from this screen.</p>
      <?php else: ?>
        <form method="POST" action="/admin/roles/<?= (int) $role['id'] ?>" class="mt-4">
          <?= csrf_field() ?>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <?php foreach ($permissionGroups as $group => $permissions): ?>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/45"><?= e(ucwords($group)) ?></p>
                <div class="mt-2 space-y-1.5">
                  <?php foreach ($permissions as $permission): ?>
                    <label class="flex items-center gap-2 text-sm">
                      <input type="checkbox" name="permissions[]" value="<?= (int) $permission['id'] ?>"
                             <?= in_array((int) $permission['id'], $grantsByRole[$role['id']], true) ? 'checked' : '' ?>
                             class="h-4 w-4 accent-leash">
                      <?= e($permission['name']) ?>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-5">Save <?= e($role['name']) ?> permissions</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php \App\Core\View::stop(); ?>
