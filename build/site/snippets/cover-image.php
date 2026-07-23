<?php
/** Project STARTER — page cover image. Replace with your markup. */
?>
<?php if ($page->cover()->isNotEmpty() && $img = $page->cover()->toFile()): ?>
<figure class="cover ratio ratio-16x9">
  <?= $img->crop(1600, 900)->html(['loading' => 'lazy', 'alt' => $img->alt()->or($page->title())->esc()]) ?>
</figure>
<?php endif ?>
