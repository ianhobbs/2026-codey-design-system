<?php
    /** @var \Kirby\Cms\App $kirby */
    /** @var \Kirby\Cms\Site $site */
    /** @var \Kirby\Cms\Page $page */
?>
<?php

/**
 * Codey heading block (core).
 *
 * Kirby's default heading snippet renders `level` and `text` and nothing else.
 * blocks/heading.yml offers five more fields — position, class, bgcolor,
 * margin, and the animation trio — and every one of them was being dropped on
 * the floor: an editor picked an option in the Panel, saved, and the page came
 * back identical. Same silent no-op as the `mysans` class option 4.0.0 fixed,
 * but across five fields instead of one.
 *
 * This snippet exists to wire them up. Nothing here is new design; it is the
 * blueprint's existing promises, kept.
 *
 * @var \Kirby\Cms\Block $block
 */

// Allowlisted, never interpolated raw: `level` reaches the markup as a TAG
// NAME, which is the one place a stored value must not be trusted.
$level = strtolower((string) $block->level()->or('h2'));
if (in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) === false) {
    $level = 'h2';
}

$text = $block->text();

// Three independent utility slots, any of which may be empty. `class` is read
// through content()->get() rather than $block->class() to keep the reserved
// word out of a method call position.
$classes = array_filter([
    $block->position()->value(),                 // text-left | text-center | text-right
    $block->content()->get('class')->value(),    // decor | subhead | down-step | …
    $block->margin()->value(),                   // my-3 md:my-5 | p-3 md:p-4 lg:p-5 | …
]);

// The blueprint stores a custom-property NAME (--color-6), not a colour, so the
// value it yields is always one core owns. `--null` is the blueprint's own
// "no colour" escape and must not become var(--null).
$bgcolor = $block->bgcolor()->value();
$bgcolor = ($bgcolor && $bgcolor !== '--null') ? 'var(' . $bgcolor . ')' : null;

$styleVars = array_filter([
    'background-color' => $bgcolor,
    '--anim-delay'     => $block->animation_delay()->value(),
    '--anim-duration'  => $block->animation_duration()->value(),
]);

$attrs = array_filter([
    'class'        => $classes ? implode(' ', $classes) : null,
    'data-animate' => $block->animation()->value() ?: null,
    'style'        => $styleVars
        ? implode('; ', array_map(fn ($k, $v) => "$k: $v", array_keys($styleVars), $styleVars)) . ';'
        : null,
]);

// Every field is optional, so a heading with no options set must come out as a
// bare <h2>, not <h2 > — the space is only earned once there is an attribute.
$attrHtml = $attrs ? ' ' . Html::attr($attrs, null, ' ') : '';

?>
<<?= $level . $attrHtml ?>><?= $text ?></<?= $level ?>>
