<?php
/** Codey core snippet: card — vanilla snippet at site/snippets/codey/card.php.
 *  Call via snippet('codey/card'). */
?>
<article class="codey-card">
  <?php if ($title ?? false): ?><h2><?= esc($title) ?></h2><?php endif ?>
  <?php if ($text ?? false): ?><p><?= esc($text) ?></p><?php endif ?>
</article>
