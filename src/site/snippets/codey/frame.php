<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey page shell (core) — the two-axis frame, slot-based. Templates call:
     *
     *   <?php snippet('codey/frame', ['pad' => 'large'], slots: true) ?>
     *   <?php slot() ?> …page content… <?php endslot() ?>
     *   <?php endsnippet() ?>
     *
     * Params:  head  'default'|'hidden'          which <head> (hidden = noindex)
     *          pad   'none'|'narrow'|'medium'|'large'   <main> vertical rhythm (layout.css)
     *          reach 'inset'|'spread'|'bleed'    how far content reaches (layout.css)
     * Slots:   default → <main> content;  intro → injected before <main>
     *
     * Named `frame`, not `layout`, on purpose. Kirby owns the word "layout" — the
     * layout FIELD TYPE, $page->layout(), blueprints/fields/layout.yml — and the
     * field renderer beside this file is `codey/layouts` for exactly that reason.
     * This snippet emits <main class="frame">, so it takes the frame's name and
     * the one-letter layout/layouts trap goes away.
     *
     * Decoupled on extraction: the project's members/accounts/user-theme logic
     * was removed. The <head> is the PROJECT's own `head` snippet; the header
     * and footer are the vanilla snippets `codey/header` / `codey/footer`.
     * `$page->theme()` expects a project `theme` field/model returning a
     * .theme-* class; falls back to the default flavor.
     */
    $pad   = $pad   ?? 'large';
    $reach = $reach ?? 'spread';
    $headSnippet = (($head ?? 'default') === 'hidden') ? 'head-hidden' : 'head';
?>
<?php snippet($headSnippet) ?>
<body lang="en" class="<?= $page->theme()->or('theme-codey') ?>" x-data="nav">

  <?php snippet('codey/header') ?>

  <?= $slots->intro ?? '' ?>

  <main class="frame" data-reach="<?= esc($reach, 'attr') ?>" data-pad="<?= esc($pad, 'attr') ?>">
  <?= $slot ?>
<?php snippet('codey/footer') /* closes </main> + renders <footer> + body-tail + </body></html> */ ?>
