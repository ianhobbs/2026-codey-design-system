<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
?>
<?php

/**
 * Hero block — a full-screen image, edge to edge.
 *
 * Why this is a separate block rather than an option on `image`:
 *
 *   The image block declares sizes="auto", which makes the browser measure the
 *   real slot — but `auto` requires loading="lazy", and lazy-loading the LCP
 *   image is the single worst thing you can do to Largest Contentful Paint. A
 *   hero therefore needs the opposite settings on every axis: eager, high
 *   fetchpriority, and an explicit `sizes` it can act on during preload scan,
 *   before layout exists. Those two configurations cannot coexist on one
 *   element, so they are two blocks.
 *
 *   Because the hero is always edge to edge, its width is known without
 *   consulting the row's grid device: 100vw, declared flat. It carries
 *   `bleed-viewport` (codey/lib/grid.css) so it bleeds from wherever the
 *   editor drops it, framed row or not.
 *
 * @var \Kirby\Cms\Block $block
 */

$image = $block->image()->toFile();

// No file, nothing to render — a hero with no image is not a layout element.
if (!$image) {
    return;
}

$alt      = $block->alt()->or($image->alt());
$caption  = $block->caption();
$height   = $block->height()->or('viewport')->value();
$position = $block->position()->or('center')->value();

// Eager + high priority is the whole point of this block; `sizes` is a flat
// 100vw because the element is always viewport-width. `fetchpriority` moves
// this ahead of stylesheets in the queue, which is what makes the LCP land.
$imgAttrs = array_filter([
    'width'         => $image->width(),
    'height'        => $image->height(),
    'loading'       => 'eager',
    'decoding'      => 'async',
    'fetchpriority' => 'high',
]);

$sizes = '100vw';

?>
<figure class="hero bleed-viewport" data-height="<?= esc($height, 'attr') ?>" data-position="<?= esc($position, 'attr') ?>">
  <picture>
    <source srcset="<?= $image->srcset('avif') ?>" sizes="<?= $sizes ?>" type="image/avif">
    <source srcset="<?= $image->srcset('webp') ?>" sizes="<?= $sizes ?>" type="image/webp">
    <img src="<?= $image->url() ?>" srcset="<?= $image->srcset('default') ?>" sizes="<?= $sizes ?>" alt="<?= $alt->esc() ?>" <?= Html::attr($imgAttrs, null, ' ') ?>>
  </picture>

  <?php if ($caption->isNotEmpty()): ?>
  <figcaption class="img-caption hero-caption">
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
