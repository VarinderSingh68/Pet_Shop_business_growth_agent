<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $user */
/** @var string|null $provisioningUri */
/** @var string|null $secretDisplay */

$isEnabled = (bool) $user['two_factor_enabled'];
$hasPendingSecret = !$isEnabled && $user['two_factor_secret'] !== null;
?>

<div class="max-w-xl space-y-6">
  <div class="card-tag p-5 bg-white">
    <div class="flex items-center gap-3">
      <span class="icon-chip <?= $isEnabled ? 'icon-chip-fern' : 'icon-chip-leash' ?>"><?= icon('shield-check', 'h-4 w-4') ?></span>
      <div>
        <p class="font-display font-semibold">Two-factor authentication</p>
        <p class="text-sm text-ink/60"><?= $isEnabled ? 'Enabled — a code from your authenticator app is required to sign in.' : 'Not enabled for your account yet.' ?></p>
      </div>
    </div>

    <?php if ($isEnabled): ?>
      <form method="POST" action="/admin/security/2fa/disable" class="mt-5 max-w-sm space-y-3 border-t border-mist pt-5">
        <?= csrf_field() ?>
        <label for="disable_password" class="block text-sm font-semibold">Confirm your password to turn it off</label>
        <input type="password" id="disable_password" name="password" required autocomplete="current-password" class="input text-sm">
        <button type="submit" class="btn btn-danger btn-sm">Disable two-factor authentication</button>
      </form>

    <?php elseif ($hasPendingSecret): ?>
      <div class="mt-5 border-t border-mist pt-5">
        <p class="text-sm">1. Add this key to an authenticator app (Google Authenticator, Authy, 1Password, etc.) — use "enter setup key manually":</p>
        <p class="mt-2 font-mono text-sm tracking-wide bg-mist/40 px-3 py-2 inline-block"><?= e($secretDisplay) ?></p>
        <?php if ($provisioningUri): ?>
          <p class="mt-2 text-xs text-ink/50 break-all">Or paste this URI if your app supports import: <?= e($provisioningUri) ?></p>
        <?php endif; ?>

        <form method="POST" action="/admin/security/2fa/confirm" class="mt-5 max-w-xs space-y-3">
          <?= csrf_field() ?>
          <label for="confirm_code" class="block text-sm font-semibold">2. Enter the 6-digit code it shows</label>
          <input type="text" id="confirm_code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required
                 class="input text-sm text-center tracking-[0.4em]" placeholder="000000">
          <button type="submit" class="btn btn-primary btn-sm">Confirm and enable</button>
        </form>
      </div>

    <?php else: ?>
      <form method="POST" action="/admin/security/2fa/setup" class="mt-5 border-t border-mist pt-5">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary btn-sm">Set up two-factor authentication</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
