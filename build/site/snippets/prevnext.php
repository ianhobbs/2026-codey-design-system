<?php
/** Project STARTER — previous/next within siblings. */
?>
<nav class="prevnext flex justify-between gap-3" aria-label="Note navigation">
  <?php if ($prev = $page->prevListed()): ?><a rel="prev" href="<?= $prev->url() ?>">&larr; <?= $prev->title()->esc() ?></a><?php endif ?>
  <?php if ($next = $page->nextListed()): ?><a rel="next" href="<?= $next->url() ?>"><?= $next->title()->esc() ?> &rarr;</a><?php endif ?>
</nav>
