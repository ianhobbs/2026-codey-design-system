<?php
/**
 * Project head — wires the override precedence via LOAD ORDER.
 *   1) main.css   → Codey core (tier 1) + brand.css (tier 2), every page
 *   2) @auto      → src/assets/css/templates/{template}.css (tier 3), scoped
 * Kirby's css('@auto') loads the per-template file only when it exists.
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $page->title() ?> — <?= $site->title() ?></title>

<?= css('assets/css/main.css') ?>
<?= css('@auto') ?>
