<?php

/**
 * Codey design system — Kirby plugin (vendored, OVERWRITE ZONE).
 *
 * Synced to a project at src/site/plugins/codey/ and mirrored to
 * build/site/plugins/codey/ by CodeKit. Registers reusable snippets, blocks,
 * blueprints, templates, and models. A project overrides any of these BY NAME
 * — Kirby resolves a site-level snippet/template over a plugin-registered one,
 * so no vendored file is ever edited to customise behaviour.
 */
Kirby::plugin('ianhobbsmedia/codey', [
    'snippets' => [
        'codey/card' => __DIR__ . '/snippets/card.php',
    ],
    // 'blueprints' => [ 'blocks/…' => __DIR__ . '/blueprints/…' ],
    // 'templates'  => [ 'note' => __DIR__ . '/templates/note.php' ],
    // 'pageModels' => [ 'note' => Codey\Note::class ],
]);
