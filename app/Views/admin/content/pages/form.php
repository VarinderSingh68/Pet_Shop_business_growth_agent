<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array|null $page */

$isEdit = $page !== null;
?>

<a href="/admin/content/pages" class="text-sm text-ink/50 hover:text-leash">&larr; Pages</a>

<form method="POST" action="<?= $isEdit ? '/admin/content/pages/' . (int) $page['id'] : '/admin/content/pages' ?>" class="mt-3 max-w-2xl card-tag p-5 bg-white space-y-4">
  <?= csrf_field() ?>
  <div>
    <label for="title" class="block text-sm font-semibold mb-1">Title</label>
    <input id="title" type="text" name="title" required value="<?= e($page['title'] ?? '') ?>" class="input">
    <?php if ($isEdit): ?><p class="text-xs text-ink/50 mt-1">URL: /legal/<?= e($page['slug']) ?></p><?php endif; ?>
  </div>
  <div>
    <label for="body" class="block text-sm font-semibold mb-1">Body</label>
    <p class="text-xs text-ink/50 mb-1">Use <code>{{store_name}}</code>, <code>{{store_address}}</code>, <code>{{store_email}}</code>, <code>{{store_phone}}</code> — filled in from Settings.</p>
    <?php \App\Core\View::include('components/rich-text-editor', ['id' => 'body', 'name' => 'body', 'value' => $page['body'] ?? '', 'rows' => 'min-h-[20rem]']); ?>
  </div>
  <label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_published" value="1" <?= ($page['is_published'] ?? 1) ? 'checked' : '' ?> class="border-2 border-ink">
    Published
  </label>
  <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create page' ?></button>
</form>

<?php \App\Core\View::stop(); ?>
