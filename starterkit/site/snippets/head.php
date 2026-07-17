<?php
/**
 * Starterkit <head> stub.
 *
 * Wire the design system's front-end layer here. Paths assume the npm packages
 * are installed (node_modules) or the compiled bundles are copied into assets/.
 * Adjust to your build/deploy setup.
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $page->title() ?> — <?= $site->title() ?></title>

<?php /* Design system front-end layer (order matters: tokens → css) */ ?>
<link rel="stylesheet" href="<?= url('assets/codey/tokens.css') ?>">
<link rel="stylesheet" href="<?= url('assets/codey/codey.css') ?>">

<?php /* Per-site brand overrides load LAST so they win the cascade */ ?>
<link rel="stylesheet" href="<?= url('assets/css/brand.css') ?>">
