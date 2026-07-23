# Codey Architectures

Front-end architecture reference for Codey. ~6 sections: page layouts, containers, grids & flexbox, off-screen, off-page layouts, UX (+1 TBD).

---

## 1. Page layouts

### Core problem
The current Codey site frames content ~80% of the time by positioning content in margins (body owns the margin → "pretty frame"). The frame is baked into the container, so switching to full-screen edge-to-edge ("content defines margins") fights the inherited frame.

### Fix — invert margin ownership
Stop padding inward on body. Make the gutter a **token** on a layout grid with named tracks; content defaults into a centered track, any block opts into full width. Mode becomes one switchable variable.

```css
.layout {
  display: grid;
  grid-template-columns:
    [full-start] minmax(var(--gutter), 1fr)
    [content-start] minmax(0, var(--measure)) [content-end]
    minmax(var(--gutter), 1fr) [full-end];
}
.layout > *      { grid-column: content; }  /* framed by default */
.layout > .bleed { grid-column: full; }      /* edge-to-edge opt-out */
```

Drive mode the Codey way: a `data-layout` attribute / Alpine state on the layout root flips `--gutter` / tracks.

### Two-axis model (pinned terminology)

The defining frame for Codey page layouts. header/main/footer and frame/bleed/spread are different axes, which is what keeps them clean. **They compose, they don't compete.**

- **Skeleton axis** — vertical. header/main/footer document landmarks stacked top to bottom (rows).
- **Track axis** — horizontal. frame/bleed/spread gutter/full-width track system (columns).

Because they're different axes they never contend for the same property; every layout is one point on each axis.

### Speculative layout series (codified, not yet locked)
- **frame** — current 80% case refactored; symmetric gutter owned by grid (not body padding), centered max-measure track.
- **bleed** — full-screen edge-to-edge; `--gutter: 0`, content supplies local padding.
- **spread** — hybrid; framed by default with per-block `.bleed` opt-outs. Resolves the mode-switch problem on a single page.
- **inset** — content-defines-margin; gutter derived from content (asymmetric rail / sidebar / image sets the column).
- **rail / split** — multi-region asymmetric shells (fixed side + fluid main).

### header / main / footer (skeleton axis)
- **Vertical skeleton** (rows): `body { min-height: 100svh; display: grid; grid-template-rows: auto 1fr auto }` → header/main/footer + sticky-footer.
- **Horizontal tracks** (the full/content columns) live inside each landmark, ideally shared via `subgrid` so a bleed background in header and bleed media in main align to the same edges. header/footer commonly = framed content over a bleed background band; main is where **spread** earns its keep.
- Menu-stack / push-stack off-page elements sit OUTSIDE this skeleton (fixed overlays over the canvas).
- Decision point: grid on body with landmarks as subgrid items (tightest alignment) vs re-applied per-landmark (looser but portable — likely better for the snippet/slot model).

### Sticky / scroll-detection header
Sticky resolves against the nearest **scroll container**, NOT a positioned ancestor — so no `position: relative` needed on body (the abspos rule does not apply). Three real grid-sticky gotchas:

1. **`align-items: stretch`** (default) stretches the item so it can't travel → set `align-self: start` on the header.
2. **Overflow trap:** `overflow-x: hidden` makes `overflow-y` compute to `auto`, creating a scroll container that breaks sticky. Contain bleed with grid tracks / max-width, never overflow.
3. **Containing-block confinement:** sticky only sticks within its parent's box; mount the header where its parent is tall (direct child of body grid is fine).

Two architectures:
- **Sticky-in-flow** — header in body row-grid, `align-self: start`, no overflow clipping above it. Natural flow, cleanest if overflow discipline holds.
- **Fixed overlay (Codey lean)** — header `position: fixed` outside the row grid, `main` gets compensating `padding-top`. Sidesteps containing-block/overflow issues; consistent with how Codey already floats fixed off-page elements.

**Scroll-detection:** use a sentinel + IntersectionObserver, Alpine flips a `data-state` attribute (`scrolled` / `pinned` / `unpinned`) that CSS transitions react to — keeps it off the scroll-event hot path. Composes with the off-screen core stack (transform + Alpine.js + CSS injection).

---

## 2. Containers

Containers are grid children of the page-layout grid.

### Subgrid = the alignment wiring
Subgrid enforces the "compose, don't compete" two-axis model. Without it, every container that declares `display: grid` invents its own independent columns → children land on the container's lines, not the page's `full`/`content` lines → gutters drift and edges stop aligning one level down.

With `grid-template-columns: subgrid` a container **adopts the parent's column tracks** (named lines `full`/`content` pass through), so even deeply nested blocks bleed to the exact same edge as top-level ones.

**Subgrid is per-axis** — a container can subgrid *columns only* (inherit the horizontal track axis) while running its *own rows* (its private skeleton). That is the two-axis split made literal: horizontal alignment propagates down, vertical structure stays local.

### Two kinds of container
- **Track-aligned** — `subgrid` on columns; inherit the gutter system; content stays on the page rhythm. Default for structural containers.
- **Independent** — own grid, intentionally breaking out of shared tracks; for self-contained objects (bleed media with internal layout, widgets, card grids) that should not inherit page gutters.

### Caveats
- Subgrid only spans the parent tracks the item actually covers (a `content`-column container can't subgrid the `full` tracks it doesn't occupy).
- `gap` is inherited but overridable.
- Browser support solid across evergreens since ~2023.

---

## 3. Grids & flexbox

**Status: note only — come back to it.**

### Kirby grid (OPEN QUESTION — needs verification)
Kirby's Layout field defaults to a **12-column grid**. Unverified whether the column count can be overwritten / specified per blueprint. No Kirby MCP is connected, so this could not be checked directly — verify against Kirby docs (layout field blueprint options) when revisited.

### The holy grail — custom weighted grids
Designers want custom asymmetric, weighted grids that respond cleanly across breakpoints — historically hard to "push into breakpoints."

Example weights per breakpoint:
- `lg: 2, 5, 7, 3, 11, 3`
- `md: 1, 2, 2, 1`
- `sm: 1`

Key insight: `lg` sums to 31, not 12 → this is NOT a 12-col fraction grid, it's a **weighted/ratio grid**. Maps directly onto CSS Grid `fr` units with a per-breakpoint track list:

```css
.grid       { display: grid; grid-template-columns: 1fr; }              /* sm */
@media (md) { .grid { grid-template-columns: 1fr 2fr 2fr 1fr; } }
@media (lg) { .grid { grid-template-columns: 2fr 5fr 7fr 3fr 11fr 3fr; } }
```

CSS does this natively; Kirby's fraction-based 12-col layout field is exactly what resists it. Likely solution: a custom layout field / blueprint that emits per-breakpoint `fr` track lists rather than 12-col fractions.

### Flexbox
TBD — likely the intra-component / content-driven counterpart to grid's page/container structure.

---

## 4. Off-screen (off-canvas core stack)

Codey's off-page (off-canvas) compositions all use a fixed core stack:

- **Transform-based motion** — `transform: translateX/translateY` for the slide (GPU-composited, no reflow).
- **Alpine.js** — drives open/close state and behaviour.
- **CSS injection** — for styling.

These three are the core elements in every Codey off-page composition.

---

## 5. Off-page layouts

In Kirby and other PHP methods, off-page items are implemented as **either snippets or slots, depending on the utility of the context**. They are loaded **late in the page cascade — last**.

```php
<?php snippet('off-canvas-nav') ?>
<?php snippet('off-canvas-content', ['field' => $page->section()]) ?>
```

### Menu-stack (common variant)
One or more off-canvas elements that translate-animate (x or y) **over** the canvas, invoked by Alpine.js events. Up to **4 off-page snippets**.

Off-canvas markup / attributes address:
- **Parent overlays** — the backdrop/overlay behind the panel.
- **Transition speed** — animation timing.
- **Coverage** — full or partial.
- **Custom shapes** — e.g. SVG backgrounds.
- **Styling**.

### Push-stack family (exotic variant)
Where a page wrapper / cell track translates within a fixed viewport rather than sliding a panel in over the page. Codified family name = **push-stack**; children named by cell count:

- **uno** — primary / single-cell.
- **duo** — two-cell track that shuttles left/right; only one cell in view at a time (one-view object).
- **trio** — three cells, if that ever arises.

Family is open-ended; name future variants by the same convention.

---

## 6. UX

TBD.

---

## (+1) Sixth section

TBD — unnamed.
