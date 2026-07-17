<?php

use Kirby\Cms\App as Kirby;

/**
 * Codey Design System — Kirby plugin entry point.
 *
 * Registers the design system's snippets, blueprints, templates, and models
 * so any Kirby project can `composer require ianhobbsmedia/codey-design-system`
 * and inherit the shared UI layer.
 *
 * Extraction target: populate the arrays below by promoting stable pieces
 * from the source project's src/site/ tree (blueprints, snippets, blocks,
 * templates, models). Keep project-specific content OUT — only reusable
 * system parts belong here.
 */
Kirby::plugin('ianhobbsmedia/codey-design-system', [
    'snippets' => [
        // 'codey/accordion'   => __DIR__ . '/snippets/accordion.php',
        // 'codey/blocks/...'  => __DIR__ . '/snippets/blocks/...',
    ],
    'blueprints' => [
        // 'blocks/...'    => __DIR__ . '/blueprints/blocks/....yml',
        // 'sections/...'  => __DIR__ . '/blueprints/sections/....yml',
    ],
    'templates' => [
        // 'note' => __DIR__ . '/templates/note.php',
    ],
    'models' => [
        // 'note' => Codey\DesignSystem\Models\Note::class,
    ],
]);
