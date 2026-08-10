<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $campaigns */
/** @var array $segments */
?>

<div class="flex gap-2 mb-4">
  <a href="/admin/marketing/segments" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Segments</a>
  <a href="/admin/marketing/campaigns" class="text-sm font-semibold border-b-2 border-ink pb-1">Campaigns</a>
  <a href="/admin/marketing/coupons" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Coupons</a>
  <a href="/admin/marketing/activity" class="text-sm font-semibold text-ink/50 hover:text-ink pb-1">Growth Agent activity</a>
</div>

<div class="grid lg:grid-cols-[1fr_360px] gap-6">
  <div class="border-2 border-ink bg-white overflow-x-auto">
    <table class="admin-table w-full text-sm">
      <thead>
        <tr class="border-b-2 border-ink">
          <th class="p-2 text-left">Name</th><th class="p-2 text-left">Segment</th><th class="p-2 text-left">Channel</th>
          <th class="p-2 text-left">Status</th><th class="p-2 text-right">Sent</th><th class="p-2 text-right">Converted</th><th class="p-2 text-right">Revenue</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campaigns as $c): ?>
          <tr class="border-b border-mist cursor-pointer hover:bg-tennis/10" onclick="window.location='/admin/marketing/campaigns/<?= (int) $c['id'] ?>'">
            <td class="p-2 font-medium"><?= e($c['name']) ?></td>
            <td class="p-2"><?= e($c['segment_name'] ?? '—') ?></td>
            <td class="p-2 capitalize"><?= e($c['channel']) ?></td>
            <td class="p-2 capitalize"><?= e($c['status']) ?></td>
            <td class="p-2 text-right"><?= (int) $c['sent_count'] ?></td>
            <td class="p-2 text-right"><?= (int) $c['converted_count'] ?></td>
            <td class="p-2 text-right font-semibold"><?= money((int) $c['revenue_paise']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if ($campaigns === []): ?>
          <tr><td colspan="7" class="p-6 text-center text-ink/50">No campaigns yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-tag p-5 bg-white h-fit">
    <p class="font-display font-semibold">New campaign</p>
    <form method="POST" action="/admin/marketing/campaigns" class="mt-3 space-y-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Campaign name" required class="input text-sm">
      <select name="segment_id" required class="input text-sm">
        <option value="">Choose a segment</option>
        <?php foreach ($segments as $s): ?>
          <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?> (<?= (int) $s['member_count'] ?>)</option>
        <?php endforeach; ?>
      </select>
      <select name="channel" class="input text-sm">
        <option value="email">Email</option>
        <option value="whatsapp">WhatsApp</option>
        <option value="sms">SMS</option>
        <option value="banner">On-site banner</option>
      </select>
      <input type="text" name="template_subject" placeholder="Subject line" class="input text-sm">
      <textarea name="template_body" rows="4" placeholder="Message body — use {{name}} for the customer's name" required class="input text-sm"></textarea>
      <button type="submit" class="w-full btn btn-secondary btn-sm">Create draft</button>
    </form>
  </div>
</div>

<?php \App\Core\View::stop(); ?>
