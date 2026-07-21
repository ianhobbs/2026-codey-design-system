<?php
/**
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Pages $pages
 */
?>
<?php snippet('layout', ['pad' => 'large'], slots: true) ?>
<?php slot() ?>
<div class="note">
<?php snippet('layouts-hero', ['field' => $page->layout()])  ?>
</div>
<div class="swiper mt-6 md:mt-7 pb-2 md:pb-6 swiper-slider">
<div class="swiper-wrapper pb-2">
  <?php
    // using the `toStructure()` method, we create a structure collection
    $items = $page->logos()->toStructure();
    // we can then loop through the entries and render the individual fields
      foreach ($items as $item): ?>
      <?php $caption = $item->caption(); ?>
      <div class="swiper-slide" style="background-color: <?= $item->bgcolor() ?>">
        <?php foreach ($item->photo()->toFiles() as $image): ?>
          <img src="<?= $image->url() ?>" alt="<?= $caption ?> logo" class="w-full h-full object-contain">
        <?php endforeach ?>
        <p class="text-2xs md:text-xs text-center text-(--color-grey) -mt-1"><?= $caption ?></p>
      </div>
    <?php endforeach ?>
</div>
<?php snippet('layouts', ['field' => $page->layout2()])  ?>
<?= js(['assets/js/swiper-play.js',]) ?>
</div>
<?php endslot() ?>
<?php endsnippet() ?>

