<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array|null $post */
/** @var array $categories */

$isEdit = $post !== null;
?>

<a href="/admin/content/blog" class="text-sm text-ink/50 hover:text-leash">&larr; Posts</a>

<form method="POST" action="<?= $isEdit ? '/admin/content/blog/' . (int) $post['id'] : '/admin/content/blog' ?>" enctype="multipart/form-data" class="mt-3 max-w-2xl card-tag p-5 bg-white space-y-4">
  <?= csrf_field() ?>
  <div>
    <label for="title" class="block text-sm font-semibold mb-1">Title</label>
    <input id="title" type="text" name="title" required value="<?= e($post['title'] ?? '') ?>" class="input">
  </div>
  <div>
    <label for="cover_image" class="block text-sm font-semibold mb-1">Cover image</label>
    <?php if (!empty($post['cover_image_path'] ?? null)): ?>
      <img src="<?= e(media_url($post['cover_image_path'])) ?>" alt="" class="mb-2 h-32 w-full max-w-xs object-cover border-2 border-ink">
    <?php endif; ?>
    <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="input">
  </div>
  <div>
    <label for="blog_category_id" class="block text-sm font-semibold mb-1">Category</label>
    <select id="blog_category_id" name="blog_category_id" class="input">
      <option value="">No category</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= ($post['blog_category_id'] ?? null) === $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="excerpt" class="block text-sm font-semibold mb-1">Excerpt</label>
    <input id="excerpt" type="text" name="excerpt" maxlength="300" value="<?= e($post['excerpt'] ?? '') ?>" class="input">
  </div>
  <div>
    <label for="body" class="block text-sm font-semibold mb-1">Body</label>
    <?php \App\Core\View::include('components/rich-text-editor', ['id' => 'body', 'name' => 'body', 'value' => $post['body'] ?? '']); ?>
  </div>
  <div>
    <label for="status" class="block text-sm font-semibold mb-1">Status</label>
    <select id="status" name="status" class="input">
      <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
      <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create post' ?></button>
</form>

<?php \App\Core\View::stop(); ?>
