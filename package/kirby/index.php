<?php

/**
 * Codey design system — Kirby plugin (vendored, OVERWRITE ZONE).
 *
 * Synced to a project at src/site/plugins/codey/ and mirrored to
 * build/site/plugins/codey/ by CodeKit. Registers the core layout shell,
 * header/footer, the layout-field renderer, the layout blueprint field, and
 * reusable snippets. A project overrides any of these BY NAME — Kirby resolves
 * a site-level snippet/template/blueprint over a plugin-registered one, so no
 * vendored file is ever edited to customise behaviour.
 */
Kirby::plugin('ianhobbsmedia/codey', [
    'snippets' => [
        'codey/layout'  => __DIR__ . '/snippets/layout.php',   // page shell (two-axis)
        'codey/header'  => __DIR__ . '/snippets/header.php',    // logo + nav
        'codey/footer'  => __DIR__ . '/snippets/footer.php',    // closes main + footer + tail
        'codey/layouts' => __DIR__ . '/snippets/layouts.php',   // layout-field renderer
        'codey/card'    => __DIR__ . '/snippets/card.php',      // sample component
    ],
    'blueprints' => [
        'fields/codey-layout' => __DIR__ . '/blueprints/fields/layout.yml',
    ],
    'templates' => [
        // Example only — a project owns its own default.php:
        // 'default' => __DIR__ . '/templates/default.php',
    ],
]);
