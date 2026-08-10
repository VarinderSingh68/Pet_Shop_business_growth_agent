<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var array $categories */
?>

<a href="/admin/content/blog" class="text-sm text-ink/50 hover:text-leash">&larr; Posts</a>
<h1 class="font-display text-xl font-semibold mt-2">Blog categories</h1>

<div class="mt-4 max-w-md">
  <ul class="space-y-2">
    <?php foreach ($categories as $c): ?>
      <li class="card-tag p-3 bg-white text-sm font-medium"><?= e($c['name']) ?></li>
    <?php endforeach; ?>
  </ul>

  <form method="POST" action="/admin/content/blog/categories" class="mt-4 flex gap-2">
    <?= csrf_field() ?>
    <input type="text" name="name" placeholder="New category name" required class="flex-1 input text-sm">
    <button type="submit" class="btn btn-secondary btn-sm">Add</button>
  </form>
</div>

<?php \App\Core\View::stop(); ?>
