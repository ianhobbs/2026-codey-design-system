<?php
    /** @var \Kirby\Cms\Site $site */
    /**
     * Codey mobile nav (core) — the slide-in drawer for narrow viewports.
     *
     * Rendered once from codey/frame, as a sibling of <header>, so every
     * template gets it without a per-template call. It must NOT live inside
     * <header>: layout.css gives that element a max-width, an auto margin and
     * a padding-inline, and a position:fixed overlay parented to it would
     * inherit a containing block it has to fight back out of.
     *
     * Alpine state is the `disclosure` component declared on <body> in
     * codey/frame — <body> is the nearest common ancestor of this drawer and
     * the toggle button in codey/header, so it is the only scope that reaches
     * both. `open` is shared; the toggle flips it, this file reads it.
     *
     * SLIDE DIRECTION is `option('codey.nav.side')` — 'right' (default) or
     * 'left'. It arrives as data-side, and nav.css re-points --nav-drawer-offset
     * and the inset from that attribute rather than restating the rules, the
     * same idiom as .hero[data-height]. A project sets it once in config.php:
     *
     *     'codey.nav.side' => 'left',
     *
     * TRANSITIONS use x-transition's class form rather than its modifiers.
     * The .opacity / .scale modifiers cannot express a slide, and the class
     * form is CSP-safe: @alpinejs/csp only calls evaluate() when the attribute
     * value is a function, so a literal class string passes through unread.
     * The classes themselves are defined in codey/lib/nav.css.
     *
     * Hidden at >= 48rem by a media query on the wrapper, so the whole subtree
     * is display:none at desktop regardless of what x-show wrote inline — an
     * inline style beats every @layer, so the wrapper is where that fight is
     * won rather than on the panel.
     *
     * x-cloak is not decoration here. head.php loads Alpine with `defer`, so
     * there is a window between parse and boot in which x-show has not yet run
     * and the scrim — a full-viewport 45% black layer — would paint over the
     * page. Alpine strips the attribute on init; nav.css hides the wrapper
     * while it is present.
     */
    $side = option('codey.nav.side', 'right');
?>
<div class="nav-drawer" id="site-nav" x-cloak data-side="<?= esc($side, 'attr') ?>">

  <div class="nav-drawer-scrim"
       x-show="open"
       x-transition.opacity.duration.200ms
       @click="close"></div>

  <nav class="nav-drawer-panel"
       aria-label="Mobile navigation"
       x-show="open"
       x-transition:enter="nav-drawer-anim"
       x-transition:enter-start="nav-drawer-out"
       x-transition:enter-end="nav-drawer-in"
       x-transition:leave="nav-drawer-anim"
       x-transition:leave-start="nav-drawer-in"
       x-transition:leave-end="nav-drawer-out"
       @keydown.escape.window="close">

    <button type="button" class="nav-drawer-close" aria-label="Close navigation" @click="close">close</button>

    <?php foreach ($site->children()->listed() as $item): ?>
    <a<?= $item->isOpen() ? ' aria-current="page"' : '' ?> href="<?= $item->url() ?>" class="nav-drawer-item"><?= $item->title()->esc() ?></a>
    <?php endforeach ?>

  </nav>
</div>
