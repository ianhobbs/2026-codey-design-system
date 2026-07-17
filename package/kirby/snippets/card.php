<?php
/** Codey core snippet: card. Overridable — a project's own snippet named
 *  `codey/card` (in src/site/snippets/) wins by Kirby precedence. */
?>
<article class="codey-card">
  <?php if ($title ?? false): ?><h2><?= esc($title) ?></h2><?php endif ?>
  <?php if ($text ?? false): ?><p><?= esc($text) ?></p><?php endif ?>
</article>
