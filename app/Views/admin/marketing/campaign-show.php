<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $campaign */
?>

<a href="/admin/marketing/campaigns" class="text-sm text-ink/50 hover:text-leash">&larr; Campaigns</a>

<div class="mt-2 flex items-center justify-between flex-wrap gap-3">
  <div>
    <h1 class="font-display text-2xl font-semibold"><?= e($campaign['name']) ?></h1>
    <p class="text-sm text-ink/60">Segment: <?= e($campaign['segment_name'] ?? '—') ?> &middot; Channel: <?= e(ucfirst($campaign['channel'])) ?> &middot; Status: <?= e(ucfirst($campaign['status'])) ?></p>
  </div>
  <?php if ($campaign['status'] === 'draft'): ?>
    <form method="POST" action="/admin/marketing/campaigns/<?= (int) $campaign['id'] ?>/send" onsubmit="return confirm('Send this campaign now?');">
      <?= csrf_field() ?>
      <button type="submit" class="bg-leash text-paper font-semibold px-6 py-2.5 hover:bg-ink">Send now</button>
    </form>
  <?php endif; ?>
</div>

<div class="mt-6 grid sm:grid-cols-4 gap-4">
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Opened</p><p class="font-display text-2xl font-bold mt-1"><?= (int) $campaign['opened_count'] ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Clicked</p><p class="font-display text-2xl font-bold mt-1"><?= (int) $campaign['clicked_count'] ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Converted</p><p class="font-display text-2xl font-bold mt-1"><?= (int) $campaign['converted_count'] ?></p></div>
  <div class="card-tag p-4 bg-white"><p class="text-xs uppercase text-ink/50 font-semibold">Revenue attributed</p><p class="font-display text-2xl font-bold mt-1"><?= money((int) $campaign['revenue_paise']) ?></p></div>
</div>

<div class="mt-6 card-tag p-5 bg-white max-w-2xl">
  <p class="font-display font-semibold">Message</p>
  <p class="text-sm text-ink/60 mt-1"><?= e($campaign['template_subject'] ?? '(no subject)') ?></p>
  <p class="text-sm mt-3 whitespace-pre-line border-t border-mist pt-3"><?= e($campaign['template_body']) ?></p>
</div>

<?php \App\Core\View::stop(); ?>
