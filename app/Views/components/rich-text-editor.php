<?php
/**
 * A small dependency-free rich text editor: a contenteditable surface with a
 * formatting toolbar, syncing into a hidden textarea so it posts like any
 * other form field. Uses document.execCommand — deprecated in spec but still
 * universally supported for this small a command set, and it keeps this
 * editor free of any new runtime dependency.
 *
 * @var string $name   form field name
 * @var string $value  current HTML content
 * @var string $id     unique id prefix for this instance
 * @var string $rows   approximate editor height (Tailwind height class), default 'min-h-[16rem]'
 */
$rows ??= 'min-h-[16rem]';
$editorId = $id . '-editor';
$buttons = [
    ['cmd' => 'bold', 'label' => 'B', 'title' => 'Bold', 'class' => 'font-bold'],
    ['cmd' => 'italic', 'label' => 'I', 'title' => 'Italic', 'class' => 'italic'],
    ['cmd' => 'underline', 'label' => 'U', 'title' => 'Underline', 'class' => 'underline'],
    ['cmd' => 'formatBlock:h2', 'label' => 'H2', 'title' => 'Heading', 'class' => ''],
    ['cmd' => 'formatBlock:h3', 'label' => 'H3', 'title' => 'Subheading', 'class' => ''],
    ['cmd' => 'formatBlock:blockquote', 'label' => '&ldquo;&rdquo;', 'title' => 'Quote', 'class' => ''],
    ['cmd' => 'insertUnorderedList', 'label' => '&bull; List', 'title' => 'Bulleted list', 'class' => ''],
    ['cmd' => 'insertOrderedList', 'label' => '1. List', 'title' => 'Numbered list', 'class' => ''],
    ['cmd' => 'link', 'label' => 'Link', 'title' => 'Insert link', 'class' => ''],
    ['cmd' => 'removeFormat', 'label' => 'Clear', 'title' => 'Clear formatting', 'class' => ''],
];
?>
<div data-rte class="border-2 border-ink">
  <div class="flex flex-wrap gap-1 border-b-2 border-ink bg-mist/30 p-1.5">
    <?php foreach ($buttons as $b): ?>
      <button type="button" data-rte-cmd="<?= e($b['cmd']) ?>" title="<?= e($b['title']) ?>"
              class="<?= e($b['class']) ?> px-2 py-1 text-xs font-semibold border border-transparent hover:border-ink bg-white"><?= $b['label'] ?></button>
    <?php endforeach; ?>
  </div>
  <div id="<?= e($editorId) ?>" contenteditable="true" class="input <?= e($rows) ?> !rounded-none !border-0 overflow-y-auto prose-content"><?= $value ?></div>
  <textarea name="<?= e($name) ?>" id="<?= e($id) ?>" class="hidden"><?= e($value) ?></textarea>
</div>
<script>
  (function () {
    const editor = document.getElementById('<?= e($editorId) ?>');
    const hidden = document.getElementById('<?= e($id) ?>');
    if (!editor || !hidden) return;

    const sync = () => { hidden.value = editor.innerHTML; };
    editor.addEventListener('input', sync);
    editor.closest('form')?.addEventListener('submit', sync);

    editor.closest('[data-rte]').querySelectorAll('[data-rte-cmd]').forEach((btn) => {
      // mousedown (not click) + preventDefault: a plain click lets the
      // button steal focus first, which collapses whatever text the user
      // had selected in the editor — so "select text, click Bold" would
      // silently bold nothing. Blocking the button's own focus keeps the
      // editor's selection intact right up to the execCommand call below.
      btn.addEventListener('mousedown', (e) => e.preventDefault());
      btn.addEventListener('click', () => {
        const [cmd, arg] = btn.dataset.rteCmd.split(':');
        if (cmd === 'link') {
          const url = window.prompt('Link URL (https://…)');
          if (url) document.execCommand('createLink', false, url);
        } else {
          document.execCommand(cmd, false, arg || null);
        }
        sync();
      });
    });
  })();
</script>
