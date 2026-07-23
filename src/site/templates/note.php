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
<?php snippet('cover-image') ?>
<article class="note">
  <div class="note-header h1 animat animatFadeInUp fadeInUp">
    <h1 class="note-title"><?= $page->title()->esc() ?></h1>
    <?php $subheading = $page->subheading(); ?>
    <?php if ($subheading->isNotEmpty()): ?>
    <p class="note-subheading"><small><?= $subheading->esc() ?></small></p>
    <?php endif ?>
   </div>
  <div class="note text">
    <?= $page->text()->toBlocks() ?>
  </div>
  <footer class="note-footer">
    <?php if (!empty($tags)): ?>
    <ul class="note-tags">
      <?php foreach ($tags as $tag): ?>
      <li>
        <a href="<?= $page->parent()->url(['params' => ['tag' => $tag]]) ?>"><?= esc($tag) ?></a>
      </li>
      <?php endforeach ?>
    </ul>
    <?php endif ?>

    <?php $date = $page->date(); ?>
    <time class="note-date text-sm my-3 md:my-5" datetime="<?= $date->toDate('c') ?>">Published on <?= $date->esc() ?></time>
  </footer>

  <?php snippet('prevnext') ?>
</article>
<?php endslot() ?>
<?php endsnippet() ?>

