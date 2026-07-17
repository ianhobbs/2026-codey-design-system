<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey page shell (core) — the two-axis layout, slot-based. Templates call:
     *
     *   <?php snippet('codey/layout', ['pad' => 'large'], slots: true) ?>
     *   <?php slot() ?> …page content… <?php endslot() ?>
     *   <?php endsnippet() ?>
     *
     * Params:  head 'default'|'hidden'         which <head> (hidden = noindex)
     *          pad  'narrow'|'medium'|'large'   <main> vertical rhythm (layout.css)
     *          mode 'spread'|'bleed'|'frame'    .layout track mode (layout.css)
     * Slots:   default → <main> content;  intro → injected before <main>
     *
     * Decoupled on extraction: the project's members/accounts/user-theme logic
     * was removed. The <head> is the PROJECT's own `head` snippet; the header
     * and footer are `codey/header` / `codey/footer` (override by name).
     * `$page->theme()` expects a project `theme` field/model returning a
     * .theme-* class; falls back to the default flavor.
     */
    $pad  = $pad  ?? 'large';
    $mode = $mode ?? 'spread';
    $headSnippet = (($head ?? 'default') === 'hidden') ? 'head-hidden' : 'head';
?>
<?php snippet($headSnippet) ?>
<body lang="en" class="<?= $page->theme()->or('theme-codey') ?>" x-data="{ showNav: false }">

  <?php snippet('codey/header') ?>

  <?= $slots->intro ?? '' ?>

  <main class="layout" data-layout="<?= esc($mode, 'attr') ?>" data-pad="<?= esc($pad, 'attr') ?>">
  <?= $slot ?>
<?php snippet('codey/footer') /* closes </main> + renders <footer> + body-tail + </body></html> */ ?>
