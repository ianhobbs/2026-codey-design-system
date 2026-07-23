<?php
/** Project STARTER — compact note card for listings. @var \Kirby\Cms\Page $note */
?>
<a class="note-small card" href="<?= $note->url() ?>">
  <h2 class="note-small-title text-lg"><?= $note->title()->esc() ?></h2>
  <?php if ($note->date()->isNotEmpty()): ?>
  <time class="text-sm" datetime="<?= $note->date()->toDate('c') ?>"><?= $note->date()->toDate('j M Y') ?></time>
  <?php endif ?>
</a>
