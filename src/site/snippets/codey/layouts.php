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
     *   grid-plain          12-col, no gap, no block margin
     *   grid-plain-padded   12-col, gap + block margin
     *   grid-cards           12-col, gap + background + radius + padding
     *   grid-bleed       edge-to-edge, auto-fit tracks — ignores --span
     *
     * Each column carries its Panel width as an inline --span, which the
     * 12-col devices consume at >=60rem. grid-bleed does not consult it:
     * column divisions play no part in a bleed-viewport row.
     *
     * The inner `.text` wrapper is a PROJECT HOOK, deliberately unstyled by
     * core: it is where a project hangs its long-form/blog rules (Rosie Boylan
     * carries that set in .styl today, to be migrated up when it matures).
     * Core ships no .text rule, so leaving it unclaimed costs nothing.
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
  <div class="column" style="--span:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
