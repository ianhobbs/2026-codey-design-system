<?php
/**
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Pages $pages
 */
?>
<?php snippet('layout', ['pad' => 'large'], slots: true) ?>
<?php slot() ?>
<?php snippet('layouts-full', ['field' => $page->layout()]) ?>
<?php endslot() ?>
<?php endsnippet() ?>
