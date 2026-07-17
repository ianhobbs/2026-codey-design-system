<?php
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey layout-field renderer (core) — renders a Kirby layout field into
     * the content track. Each layout row is a .blocks-grid (12 tracks); a
     * .column spans its --columns width (from the Panel layout field).
     *
     * Usage:  <?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
     *
     * The row's theme attr (from blueprints/fields/layout.yml) becomes a class
     * on the <section>, so a project can style plain/card/padded variants.
     */
?>
<?php foreach ($field->toLayouts() as $layout): ?>
<?php $layoutTheme = $layout->attrs()->theme()->value() ?>
<section class="blocks-grid <?= esc($layoutTheme, 'attr') ?>" id="<?= esc($layout->id(), 'attr') ?>">
  <?php foreach ($layout->columns() as $column): ?>
  <div class="column" style="--columns:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
