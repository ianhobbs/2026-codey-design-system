<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
?>
<?php snippet('layout', ['pad' => 'large'], slots: true) ?>
<?php slot() ?>
<!-- im the default template -->
 <?php snippet("layouts-full", ["field" => $page->layout()]); ?>
 <?php endslot() ?>
<?php endsnippet() ?>

