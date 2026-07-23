<?php
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey header (core) — logo (site title) + primary nav + mobile toggle.
     * Structural only: project decoration (logo SVG, social + mobile-nav
     * snippets) was stripped on extraction. Override by name (a project
     * `codey/header` snippet wins) or add the decoration back here.
     * Uses the Alpine `showNav` state declared on <body> in codey/layout.
     */
?>
  <header>
    <a class="logo flex items-center" href="<?= $site->url() ?>">
      <?= $site->title() ?>
    </a>

    <nav class="menu flex items-center" aria-label="Main Navigation" id="nav-buttons">
      <div class="hidden md:flex items-center nav-bar">
        <?php foreach ($site->children()->listed() as $item): ?>
        <a<?php e($item->isOpen(), ' aria-current="page"') ?> href="<?= $item->url() ?>" class="nav-bar-item"><?= $item->title()->esc() ?></a>
        <?php endforeach ?>
      </div>
      <button class="nav-toggle md:hidden" aria-label="Toggle navigation" @click="showNav = !showNav">menu</button>
    </nav>
  </header>
