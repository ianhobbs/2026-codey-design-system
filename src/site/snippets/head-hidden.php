<?php
/** Project <head> (noindex) — STARTER. Used when codey/layout is called with head:'hidden'. */
?>
<!DOCTYPE html>
<html lang="<?= kirby()->language()?->code() ?? 'en' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $page->title()->esc() ?> — <?= $site->title()->esc() ?></title>
  <?= css(['assets/css/main.css', '@auto']) ?>
  <?= js('assets/js/codey/alpine.js', ['defer' => true]) ?>
</head>
