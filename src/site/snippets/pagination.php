<?php
/** Project STARTER — pagination controls. @var \Kirby\Cms\Pagination|null $pagination */
?>
<?php if ($pagination && $pagination->pages() > 1): ?>
<nav class="pagination flex gap-3 justify-center" aria-label="Pagination">
  <?php if ($pagination->hasPrevPage()): ?><a rel="prev" href="<?= $pagination->prevPageURL() ?>">Newer</a><?php endif ?>
  <span><?= $pagination->page() ?> / <?= $pagination->pages() ?></span>
  <?php if ($pagination->hasNextPage()): ?><a rel="next" href="<?= $pagination->nextPageURL() ?>">Older</a><?php endif ?>
</nav>
<?php endif ?>
