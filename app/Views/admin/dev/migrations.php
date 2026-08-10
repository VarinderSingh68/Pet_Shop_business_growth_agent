<?php
\App\Core\View::extend('layouts/admin');
\App\Core\View::start('content');

/** @var string $output */
?>

<a href="/admin/dev" class="text-sm text-ink/50 hover:text-leash">&larr; Developer tools</a>
<h1 class="font-display text-xl font-semibold mt-2">Migrations</h1>

<div class="mt-4 flex flex-wrap gap-2">
  <form method="POST" action="/admin/dev/migrations/run"><?= csrf_field() ?><input type="hidden" name="action" value="up">
    <button type="submit" class="btn btn-secondary btn-sm">Run pending (up)</button>
  </form>
  <form method="POST" action="/admin/dev/migrations/run" onsubmit="return confirm('Roll back the last batch?');"><?= csrf_field() ?><input type="hidden" name="action" value="down">
    <button type="submit" class="btn btn-secondary btn-sm">Roll back last batch</button>
  </form>
  <form method="POST" action="/admin/dev/migrations/run" onsubmit="return confirm('This drops every table and rebuilds from scratch. Continue?');"><?= csrf_field() ?><input type="hidden" name="action" value="fresh">
    <button type="submit" class="border-2 border-leash text-leash px-4 py-2 text-sm font-semibold hover:bg-leash hover:text-paper">Fresh (drop + rebuild)</button>
  </form>
  <form method="POST" action="/admin/dev/migrations/run" onsubmit="return confirm('This drops every table, rebuilds, and reseeds demo data. Continue?');"><?= csrf_field() ?><input type="hidden" name="action" value="fresh-seed">
    <button type="submit" class="border-2 border-leash text-leash px-4 py-2 text-sm font-semibold hover:bg-leash hover:text-paper">Fresh + seed</button>
  </form>
</div>

<pre class="mt-4 bg-ink text-paper text-xs p-4 overflow-x-auto whitespace-pre-wrap"><?= e($output) ?></pre>

<?php \App\Core\View::stop(); ?>
