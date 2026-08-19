<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey overlay frame (core) — RESERVED, not yet wired up.
     *
     * Intended design: a full-frame top image in a custom section built from
     * TWO OVERLAPPING GRIDS — the image occupying the full track while a second
     * grid sits over it, so type and media share one optical field instead of
     * stacking. More on this when the design lands.
     *
     * Named `frame-overlay`, not `frame-hero`: the overlapping-grid device is
     * what this is, and "hero" now belongs to the block that ships one
     * (`codey/blocks/hero`). Two unrelated things called hero is the exact
     * ambiguity v4.0.0 spent a release removing — a full-screen image is a
     * BLOCK an editor drops into a row, while this is a FRAME that replaces
     * the page's layout-field renderer. Renaming cost nothing: it was
     * referenced from nowhere.
     *
     * Until then this is the plain layout-field renderer with an overlay-
     * specific name, kept deliberately rather than deleted. Its sibling
     * `layouts-full` was removed in the same pass: it was referenced from
     * nowhere and differed from `codey/layouts` only by a wrapper element.
     *
     * Usage (when built):
     *   <?php snippet('codey/frame-overlay', ['field' => $page->layout()]) ?>
     */
?>
<?php foreach ($field->toLayouts() as $layout): ?>
<?php $gridDevice = $layout->attrs()->theme()->value() ?>
<section class="<?= esc($gridDevice, 'attr') ?>" id="<?= esc($layout->id(), 'attr') ?>">
  <?php foreach ($layout->columns() as $column): ?>
  <div class="column" style="--span:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
