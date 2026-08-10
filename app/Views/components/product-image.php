<?php
/**
 * @var array<string, mixed> $product
 * @var string $class
 */
$class ??= 'aspect-square';

$palette = [
    'dog' => ['bg' => 'bg-leash', 'text' => 'text-paper'],
    'cat' => ['bg' => 'bg-fern', 'text' => 'text-paper'],
    'bird' => ['bg' => 'bg-tennis', 'text' => 'text-ink'],
    'fish' => ['bg' => 'bg-ink', 'text' => 'text-paper'],
    'small_pet' => ['bg' => 'bg-mist', 'text' => 'text-ink'],
    'other' => ['bg' => 'bg-mist', 'text' => 'text-ink'],
];
$tone = $palette[$product['pet_type']] ?? $palette['other'];

$icons = [
    'dog' => 'M4.5 12.5c0-3 2.7-6.5 7.5-6.5s7.5 3.5 7.5 6.5c0 1-1 1.5-1 2.5 0 1.7-1.3 3-3 3-1 0-1.3-.5-3.5-.5s-2.5.5-3.5.5c-1.7 0-3-1.3-3-3 0-1-1-1.5-1-2.5Zm3-3.2c-.6 0-1 .6-1 1.4s.4 1.4 1 1.4 1-.6 1-1.4-.4-1.4-1-1.4Zm9 0c-.6 0-1 .6-1 1.4s.4 1.4 1 1.4 1-.6 1-1.4-.4-1.4-1-1.4Z',
    'cat' => 'M6 3 4 9l4 1.5V19a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-8.5L20 9l-2-6-4.5 3h-3.5L6 3Zm2.5 9.5c.4 0 .8.4.8.9s-.4.9-.8.9-.8-.4-.8-.9.4-.9.8-.9Zm7 0c.4 0 .8.4.8.9s-.4.9-.8.9-.8-.4-.8-.9.4-.9.8-.9ZM12 14l1 1.3H11L12 14Z',
    'bird' => 'M4 13c0-4 3.5-7.5 8-7.5.6-1 1.7-1.7 3-1.7 0 1-.4 1.8-1 2.4 2.3 1.1 3.8 3.4 3.8 6 0 .5-.4.8-.8.8-.5 0-1.3-.3-1.7-.6L14 14l-2.5 1.3L9 14l-1.5 2H6l1-2.3C5 13.2 4 13 4 13Zm3.5 6c1-.3 2-1 2.7-2 .6.5 1.4.8 2.3.8 2.8 0 5-2 5.8-4.6.6.4 1.2 1 1.2 1.8 0 2.8-3.5 6-8 6-1.6 0-3-.4-4-.7Z',
    'fish' => 'M3 12c3-3.5 7-5.5 11-4.5 2 .5 3.5 2 4.5 3-1 1-2.5 2.5-4.5 3-4 1-8-1-11-4.5Zm14.5-1.5 3-2.5-.5 3 .5 3-3-2.5ZM8 11c.4 0 .8.4.8.9s-.4.9-.8.9-.8-.4-.8-.9.4-.9.8-.9Z',
    'small_pet' => 'M7 10c0-2.5 2-4.5 5-4.5s5 2 5 4.5c0 1-1.5 1.5-1.5 3v2c0 2-1.5 3.5-3.5 3.5s-3.5-1.5-3.5-3.5v-2c0-1.5-1.5-2-1.5-3Zm2-2c-.4 0-.7.5-.7 1.2S8.6 10.4 9 10.4s.7-.5.7-1.2S9.4 8 9 8Zm6 0c-.4 0-.7.5-.7 1.2s.3 1.2.7 1.2.7-.5.7-1.2S15.4 8 15 8Z',
    'other' => 'M12 4 4 8v8l8 4 8-4V8L12 4Zm0 2.2 5.8 2.9L12 12l-5.8-2.9L12 6.2Z',
];
$icon = $icons[$product['pet_type']] ?? $icons['other'];
?>
<div class="<?= e($class) ?> <?= e($tone['bg']) ?> <?= e($tone['text']) ?> flex items-center justify-center relative overflow-hidden">
  <svg viewBox="0 0 24 24" fill="currentColor" class="w-1/3 h-1/3 opacity-90" aria-hidden="true"><path d="<?= $icon ?>"/></svg>
  <span class="absolute bottom-2 right-2 text-[10px] font-semibold uppercase tracking-wide opacity-60"><?= e($product['pet_type']) ?></span>
</div>
