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
<?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
<?php endslot() ?>
<?php endsnippet() ?>
