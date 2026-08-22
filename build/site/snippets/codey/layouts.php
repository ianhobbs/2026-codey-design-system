<?php
    /** @var \Kirby\Cms\Page $page */
    /**
     * Codey layout-field renderer (core) — renders a Kirby layout field into
     * the content track.
     *
     * Usage:  <?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
     *
     * The row's `theme` attr (from blueprints/fields/layout.yml) is emitted as
     * the section's class, and THAT CLASS IS THE GRID — see lib/grid.css. The
     * renderer deliberately invents no class of its own: choosing the device
     * is the editor's decision in the Panel, not the renderer's.
     *
     *   grid-plain          12-col, no gap, no block margin
     *   grid-plain-padded   12-col, gap + block margin
     *   grid-cards           12-col, gap + background + radius + padding
     *   grid-bleed       edge-to-edge, auto-fit tracks — ignores --span
     *
     * The row's `reach` attr is emitted ALONGSIDE it, from the second Panel
     * select. Two attrs, two classes, because they answer two independent
     * questions — `theme` how the row divides its width, `reach` where the row
     * sits on the page's horizontal axis. Both are class names already; the
     * renderer still adds nothing, it just concatenates what the Panel chose.
     *
     *   (empty)      framed — the default, no class, byte-identical to before
     *   reach-wide   partial bleed, ~30% of the way out, >=780px (lib/layout.css)
     *   bleed        edge to edge — the class .frame > .bleed already honours
     *
     * Each class is escaped SEPARATELY and joined with a literal space, rather
     * than escaping one joined string. esc(…, 'attr') escapes a space to
     * `&#x20;`, which a browser does decode back — attribute values are parsed
     * with character references resolved — so `class="a&#x20;b"` really is two
     * classes. But it makes something structural depend on entity decoding and
     * reads as one weird class name to anyone viewing source. Escaping the
     * parts keeps the separator a separator.
     *
     * The array_filter also means an empty `reach` contributes nothing at all:
     * a framed row emits byte-for-byte what it emitted before this field
     * existed, with no trailing space inside class="".
     *
     * Each column carries its Panel width as an inline --span, which the
     * 12-col devices consume at >=60rem. grid-bleed does not consult it:
     * column divisions play no part in a bleed-viewport row.
     *
     * The inner `.text` wrapper is a PROJECT HOOK, deliberately unstyled by
     * core: it is where a project hangs its long-form/blog rules (Rosie Boylan
     * carries that set in .styl today, to be migrated up when it matures).
     * Core ships no .text rule, so leaving it unclaimed costs nothing.
     *
     * Framing is a separate axis handled by lib/layout.css — a row is placed
     * on the content track by default, or the full track when its class is a
     * bleed helper. Nothing here needs to know which.
     */
?>
<?php foreach ($field->toLayouts() as $layout): ?>
<?php $layoutTheme = $layout->attrs()->theme()->value() ?>
<?php $layoutReach = $layout->attrs()->reach()->value() ?>
<?php $rowClass = implode(' ', array_map(
        fn ($c) => esc($c, 'attr'),
        array_filter([$layoutTheme, $layoutReach], fn ($c) => (string) $c !== '')
      )) ?>
<section class="<?= $rowClass ?>" id="<?= esc($layout->id(), 'attr') ?>">
  <?php foreach ($layout->columns() as $column): ?>
  <div class="column" style="--span:<?= esc($column->span(), 'css') ?>">
    <div class="text">
      <?= $column->blocks() ?>
    </div>
  </div>
  <?php endforeach ?>
</section>
<?php endforeach ?>
