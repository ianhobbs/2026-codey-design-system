<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey hero frame (core) — RESERVED, not yet wired up.
     *
     * Intended design: a full-frame top image in a custom section built from
     * TWO OVERLAPPING GRIDS — the image occupying the full track while a second
     * grid sits over it, so type and media share one optical field instead of
     * stacking. More on this when the design lands.
     *
     * Until then this is the plain layout-field renderer with a hero-specific
     * name, kept deliberately rather than deleted. Its sibling `layouts-full`
     * was removed in the same pass: it was referenced from nowhere and differed
     * from `codey/layouts` only by a wrapper element.
     *
     * Usage (when built):
     *   <?php snippet('codey/frame-hero', ['field' => $page->layout()]) ?>
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
