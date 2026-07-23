<?php
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey layout-field renderer (core) — renders a Kirby layout field into
     * the content track.
     *
     * Usage:  <?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
     *
     * The row's `theme` attr (from blueprints/fields/layout.yml) is emitted as
     * the section's class, and THAT CLASS IS THE GRID — see lib/grid.css. The
     * renderer deliberately adds no grid class of its own: choosing the device
     * is the editor's decision in the Panel, not the renderer's.
     *
     *   plain-blocks          12-col, no gap, no block margin
     *   plain-blocks-padded   12-col, gap + block margin
     *   card-blocks           12-col, gap + background + radius + padding
     *   full-bleed-grid       edge-to-edge, auto-fit tracks — ignores --columns
     *
     * Each column carries its Panel width as an inline --columns, which the
     * 12-col devices consume at >=60rem. full-bleed-grid does not consult it:
     * column divisions play no part in a full-bleed row.
     *
     * Framing is a separate axis handled by lib/layout.css — a row is placed
     * on the content track by default, or the full track when its class is a
     * bleed helper. Nothing here needs to know which.
     */
?>
<?php foreach ($field->toLayouts() as $layout): ?>
<?php $layoutTheme = $layout->attrs()->theme()->value() ?>
<section class="<?= esc($layoutTheme, 'attr') ?>" id="<?= esc($layout->id(), 'attr') ?>">
  <?php foreach ($layout->columns() as $column): ?>
  <div class="column" style="--columns:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
