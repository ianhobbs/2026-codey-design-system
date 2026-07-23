<?php
/**
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Pages $pages
 */
?>
<?php snippet('codey/layout', ['pad' => 'large'], slots: true) ?>
<?php slot() ?>
<?php if (empty($tag) === false): ?>
<header class="h1">
  <h1>
    <small>Tag:</small> <?= esc($tag) ?>
    <a href="<?= $page->url() ?>" aria-label="All Notes">&times;</a>
  </h1>
</header>
<?php else: ?>
  <?php snippet('intro') ?>
<?php endif ?>

<ul class="grid gap-3 md:gap-5 w-full mx-auto">
  <?php foreach ($notes as $note): ?>
  <li class="column" style="--columns: 4">
      <?php snippet('note-small', ['note' => $note]) ?>
  </li>
  <?php endforeach ?>
</ul><?php snippet('pagination', ['pagination' => $notes->pagination()]) ?>
<?php endslot() ?>
<?php endsnippet() ?>

