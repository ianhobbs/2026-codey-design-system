<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
?>
<?php /* sections render directly into <main class="layout"> → content track;
         a "full-bleed-grid" layout lands on the full track (see layout.css). */ ?>
<?php foreach ($field->toLayouts() as $layout): ?>
<?php $layoutTheme = $layout->attrs()->theme()->value() ?>
<div class="<?= esc($layoutTheme, 'attr') ?>" id="<?= esc($layout->id(), 'attr') ?>" >
  <?php foreach ($layout->columns() as $column): ?>
  <div class="column" style="--columns:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</div>
<?php endforeach ?>
