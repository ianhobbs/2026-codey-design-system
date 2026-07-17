<?php
    /** @var \Kirby\Cms\Page $page */
    /**
     * Example default template (core) — shows the layout shell + field renderer.
     * NOT registered by default (a project usually owns its own default.php).
     * Copy into a project's src/site/templates/ or register in index.php.
     */
?>
<?php snippet('codey/layout', ['pad' => 'large'], slots: true) ?>
<?php slot() ?>
  <?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
<?php endslot() ?>
<?php endsnippet() ?>
