<?php
/**
 * Project <head> — STARTER, yours to edit. Codey core (codey/layout) calls this
 * by name; it is intentionally NOT part of codey/ core. Links the compiled
 * stylesheet (build/assets/css/main.css) and Alpine.
 * @var \Kirby\Cms\Page $page  @var \Kirby\Cms\Site $site
 */
?>
<!DOCTYPE html>
<html lang="<?= kirby()->language()?->code() ?? 'en' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page->isHomePage() ? $site->title()->esc() : $page->title()->esc() . ' — ' . $site->title()->esc() ?></title>
  <?php if ($site->description()->isNotEmpty()): ?>
  <meta name="description" content="<?= $site->description()->esc('attr') ?>">
  <?php endif ?>
  <?= css(['assets/css/main.css', '@auto']) ?>
  <?= js('assets/js/codey/alpine.js', ['defer' => true]) ?>
</head>
