<?php
/**
 * @var int $page
 * @var int $totalPages
 * @var string $basePath
 * @var array<string, mixed> $query Extra query params to preserve (e.g. ['status' => 'pending']), 'page' excluded automatically.
 */
$query ??= [];
unset($query['page']);
$query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
?>
<?php if ($totalPages > 1): ?>
  <nav class="mt-4 flex flex-wrap gap-2" aria-label="Pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php $href = $basePath . '?' . http_build_query([...$query, 'page' => $i]); ?>
      <a href="<?= e($href) ?>" class="w-8 h-8 flex items-center justify-center border-2 text-sm transition-colors duration-150 <?= $i === $page ? 'border-leash bg-leash text-paper' : 'border-ink hover:border-leash' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>
