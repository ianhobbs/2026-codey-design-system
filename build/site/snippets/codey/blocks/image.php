<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
?>
<?php

/** @var \Kirby\Cms\Block $block */

$alt     = $block->alt();
$caption = $block->caption();
$crop    = $block->crop()->isTrue();
$link    = $block->link();
$ratio     = $block->ratio()->or('auto');
$animation = $block->animation()->value();
$delay     = $block->animation_delay()->value();
$duration  = $block->animation_duration()->value();
$src       = null;
$image     = null;

if ($block->location()->value() === 'web') {
    $src = $block->src();
} elseif ($image = $block->image()->toFile()) {
    $alt = $block->alt()->or($image->alt());
    $src = $image->url();
}

?>
<?php if ($src):
  /* ── sizes ───────────────────────────────────────────────────────────
     `auto` makes the browser measure the image's REAL laid-out width and pick
     against that, instead of against a viewport guess. It is the only
     container-responsive mechanism `sizes` has — container-query units are
     invalid here, because `sizes` is resolved before layout exists.

     Why that matters in this layout engine: the same hardcoded 80vw was both
     too small and too large depending on the row's grid device. In a
     `grid-bleed` row the slot is 100vw (lib/grid.css gives picture/img
     width:100%), so 80vw under-selected and the browser upscaled the result;
     in a framed row the content track caps at --measure-page (48rem here), so
     80vw over-fetched by more than 2x. `auto` gets both right with no coupling
     between the block and the row theme.

     `auto` requires loading="lazy" per spec. That is exactly why the hero
     block is separate: an LCP image must not be lazy, so it declares an
     explicit 100vw instead of measuring.

     The viewport list after it is the fallback. Browsers without `auto`
     support drop that first entry as invalid and parse the rest, landing on a
     viewport guess rather than on no `sizes` at all.

     That fallback list is the one VALUE in this file, so it is an option
     rather than a literal — a project whose measure differs sets
     `codey.blocks.image.sizes` in config.php instead of editing core. */
  $sizes     = option('codey.blocks.image.sizes', 'auto, (min-width: 900px) 80vw, 90vw');
  $styleVars = array_filter(['--anim-delay' => $delay, '--anim-duration' => $duration]);
  $animStyle = $styleVars ? implode('; ', array_map(fn($k, $v) => "$k: $v", array_keys($styleVars), $styleVars)) . ';' : null;
  $attrs     = array_filter([
    'data-ratio'    => $ratio,
    'data-crop'     => $crop ?: null,
    'data-animate'  => $animation ?: null,
    'style'         => $animStyle,
  ]);
  /* Intrinsic dimensions reserve the box before the bytes land (CLS). Paired
     with `max-w-full h-auto` the browser derives the aspect ratio from these
     and scales height with width, so they constrain nothing. */
  $imgAttrs = array_filter([
    'class'    => 'max-w-full h-auto',
    'width'    => $image?->width(),
    'height'   => $image?->height(),
    'loading'  => 'lazy',
    'decoding' => 'async',
  ]); ?>
<figure class="flex flex-col items-center" <?= Html::attr($attrs, null, ' ') ?>>

  <?php if ($link->isNotEmpty()): ?>
  <a href="<?= Str::esc($link->toUrl()) ?>">
  <?php endif ?>

    <?php if ($image): ?>
    <picture>
      <source srcset="<?= $image->srcset('avif') ?>" sizes="<?= $sizes ?>" type="image/avif">
      <source srcset="<?= $image->srcset('webp') ?>" sizes="<?= $sizes ?>" type="image/webp">
      <?php /* The fallback needs its own srcset too: without one, a browser
               that takes this branch downloads the untouched master. */ ?>
      <img src="<?= $src ?>" srcset="<?= $image->srcset('default') ?>" sizes="<?= $sizes ?>" alt="<?= $alt->esc() ?>" <?= Html::attr($imgAttrs, null, ' ') ?>>
    </picture>
    <?php else: ?>
    <?php /* Remote URL — no Kirby file, so no thumbs and no intrinsic size. */ ?>
    <img class="max-w-full h-auto" src="<?= $src ?>" alt="<?= $alt->esc() ?>" loading="lazy" decoding="async">
    <?php endif ?>

  <?php if ($link->isNotEmpty()): ?>
  </a>
  <?php endif ?>

  <?php if ($caption->isNotEmpty()): ?>
  <figcaption class="img-caption">
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
