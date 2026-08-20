<?php
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey header (core) — logo (site title) + primary nav + mobile toggle.
     * Structural only: project decoration (logo SVG, social + mobile-nav
     * snippets) was stripped on extraction. Override by name (a project
     * `codey/header` snippet wins) or add the decoration back here.
     * Uses the Alpine `disclosure` component declared on <body> in codey/frame
     * (registered in src/assets/js/codey/alpine.js — CSP build, so directives
     * take method/property names only, never inline expressions).
     */
?>
  <header>
    <a class="logo" href="<?= $site->url() ?>">
      <?= $site->title() ?>
    </a>

    <nav class="menu" aria-label="Main Navigation">
      <div class="nav-bar">
        <?php foreach ($site->children()->listed() as $item): ?>
        <a<?= $item->isOpen() ? ' aria-current="page"' : '' ?> href="<?= $item->url() ?>" class="nav-bar-item"><?= $item->title()->esc() ?></a>
        <?php endforeach ?>
      </div>
      <button type="button" class="nav-toggle" aria-label="Toggle navigation"
              aria-controls="site-nav" :aria-expanded="expanded"
              @click="toggle">menu</button>
    </nav>
  </header>
