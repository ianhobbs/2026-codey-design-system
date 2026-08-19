# Implementing the Codey Design System

A practical, element-by-element guide to the Codey design system — tokens, colour,
layout, typography, elements, component seeds, and the Kirby layout engine — with
the reasoning behind each so you know not just *what* to type but *why*.

> **Delivery note.** Codey is a **Git starter you clone** to begin a project (see
> the [README](../README.md) for the clone/build quickstart and
> [ARCHITECTURE](ARCHITECTURE.md) for the rationale). It is no longer a Composer
> package or a synced kit. Sections 4–14 and the appendices below are the system
> reference and apply unchanged; sections 1–3 describe getting started.

---

## 1. The mental model (read this first)

Codey is a **starter project**: you `git clone` it and the clone *is* your site.
The theme's files already live in `src/`, and the project's own build compiles
`src/` → `build/`, which Kirby serves. Nothing is "installed into" the project.

`src/` is the single source of truth. Within it, anything under a **`codey/`
folder is the system core** (versioned — update by pulling, don't edit); everything
else is yours. Rules point **downward only**: a rule references a token below it,
never up into your project. That is what lets you re-skin by overriding tokens in
`_brand.css` instead of editing core files.

The layer stack, top (yours) to bottom (foundational):

```
your project      main.css, _brand.css, templates, snippets   ← you edit this
layout engine     codey/layout shell, header/footer, layout renderer
CSS core          layout frame, grid, typography, elements
Colour system     palettes + semantic themes
Tokens            @theme Utopia type/space, :root globals
```

## 2. Prerequisites

- **Kirby 5 / PHP 8.2+** (pulled by Composer into `build/`).
- **Node 18+ and Tailwind CSS v4** — Codey's tokens are authored as Tailwind v4
  `@theme` blocks (the `@theme`, `@source`, `@layer`, `@import "tailwindcss"`
  syntax is v4-only).
- **CodeKit** (or the bundled `npm run build`) for the `src/ → build/` compile.

## 3. Getting started

```bash
git clone <codey-repo-url> my-site && cd my-site
cd build && composer install && cd ..   # Kirby + PHP deps (Composer lives in build/)
npm install
npm run build                           # src/assets → build/assets, src/site → build/site
npm run serve                           # http://localhost:8000  (Panel at /panel)
```

The server runs this repo directly, so the compiled `build/assets` and `build/site`
mirror ARE committed. `build/kirby`, `build/vendor`, `build/site/plugins` (Composer-
installed), fonts/images (rsynced) and `build/content` (runtime data) are
gitignored. Edit `src/`, never `build/` — and treat `codey/` folders as core. Full
git-hygiene + core-vs-yours tables in the [README](../README.md).

---

## 4. The override contract — load order is precedence

This is the rule that makes everything else safe. You never edit files inside a
`codey/` zone. Instead you override in three project-owned tiers, and **load order
decides who wins**:

1. **Core (tier 1)** — your `main.css` does `@import "./codey/index.css"`.
2. **Project global (tier 2)** — `@import "./_brand.css"` *last* in `main.css`. Its
   `@theme` block overrides tokens; Tailwind v4 merges `@theme` blocks and the
   **last declaration of a token wins**. This is where a per-project Utopia rescale
   or a colour rebrand lives.
3. **Per-template (tier 3)** — `src/assets/css/templates/{template}.css`,
   auto-loaded only on that template via Kirby's `css('@auto')`. Uses `var(--token)`
   at runtime; if it needs `@apply`, it must begin with `@reference "tailwindcss";`
   because it compiles standalone.

On the **Kirby side** the mechanism is different but the principle is identical:
the plugin registers its snippets, blueprints and templates by logical name, and
**a project file of the same name always wins**. Kirby resolves
`site/snippets|blueprints|templates/` before consulting plugin registrations, so
overriding `blocks/heading` or `codey/layout` means creating that file in your
project — nothing to configure, and no vendored PHP to edit in place.

Precedence, highest first: **project files → this plugin → Kirby core.**

To refine the system itself, you change it *upstream* in the package repo, tag a
release, and `composer update`. You never fork it into your project.

---

## 5. The CSS entry file (`main.css`)

`main.css` is project-owned and is the single place that owns the cascade contract,
the `@source` globs, and the load order. A minimal, correct entry:

```css
@import "tailwindcss";

/* Cascade contract: later layer = higher priority. */
@layer theme, base, components, utilities, bespoke;

/* Project-specific content globs so Tailwind sees your class usage. */
@source "../../site/**/*.php";
@source "../../../build/content/**/*.txt";
@source "../js/**/*.js";

/* 1 ── Codey core (vendored) — one line pulls the whole manifest. */
@import "./codey/index.css";

/* 2 ── Project global override — LAST, so its @theme wins. */
@import "./_brand.css";
```

The `@layer` declaration establishes the priority order Codey's core files assume:
`theme < base < components < utilities < bespoke`. `bespoke` sits *above* utilities
so authoritative widgets (layout frame, encapsulated components) can't be
accidentally overridden by a utility class.

---

## 6. The opinionated manifest (`codey/index.css`)

`index.css` is the toggle sheet. **Core is always on; optional components ship
commented out.** You uncomment only what your markup actually uses — no accordion
markup means the accordion line stays commented and ships zero bytes.

Core (always imported):

```css
@import "./theme.css"  layer(base);            /* @theme Utopia type/space + font tokens */
@import "./globals.css";                       /* :root globals (unlayered); fonts = project override */
@import "./themes/theme-codey.css";            /* colour flavour (pulls _codey palette) */
@import "./lib/layout.css"     layer(bespoke); /* two-axis grid / page frame */
@import "./lib/typography.css" layer(base);    /* typographic base */
@import "./lib/elements.css"   layer(base);    /* aspect-ratio media boxes */
```

Optional (shipped commented — uncomment when the markup is present):

```css
/* @import "./lib/transitions.css";       generic motion tokens */
/* @import "./lib/form.css";              form field/button tokens */
/* @import "./lib/accordion.css";         disclosure motion tokens */
/* @import "./lib/cards.css";             card tokens */
/* @import "./lib/prose.css" layer(bespoke);  rich-text `.text` (extract pending) */
```

**Important:** `index.css` is inside a synced zone, so edits to it are wiped on the
next `composer update`. If you need to toggle an optional component *permanently*,
don't edit `index.css` — instead `@import` the specific `codey/lib/*.css` file from
your project-owned `main.css` (after `codey/index.css`), or enable it upstream in
the package.

---

## 7. Design tokens (`codey/theme.css`)

`theme.css` is a Tailwind v4 `@theme` block that **replaces** Tailwind's built-in
scales with a re-engineered Utopia fluid ramp, then resets and augments.

**Fluid type scale** — `--text-xs` … `--text-8xl`, each a `clamp()` that scales with
the viewport (no breakpoints needed):

```
--text-xs   --text-sm   --text-base   --text-lg   --text-xl
--text-2xl  --text-3xl  --text-4xl    --text-5xl  --text-6xl  --text-7xl  --text-8xl
```

**Fluid spacing scale** — a numeric ramp `--spacing-4xs`, `--spacing-1` …
`--spacing-17`, plus Utopia "one-up pair" steps that interpolate between two sizes:
`--spacing-3-s`, `--spacing-4-m`, `--spacing-5-l`, `--spacing-6-xl`, …
`--spacing-14-9xl`. Use the pair steps where you want a size that grows *faster*
than the single steps.

**Resets** — Codey wipes Tailwind's defaults so only its tokens resolve:

```css
--color-*: initial;  --font-*: initial;  --spacing: initial;
--font-weight: initial;  --font-weight-*: initial;
```

**Augments** — extra tokens layered on top: a set of `--leading-*`
line-heights (`tight`, `mid`, `mad`, `big`, `tighter`, `base`, `head`), the font
stacks (`--font-body`, `--font-body-medium`, `--font-head`, `--font-subhead`, `--font-italic`,
each with a matching `--*-weight`, over a
`--font-fallback`), and `--blur`, `--glass-transparency`, `--radius-lg`,
`--border-radius`.

**Rescaling per project:** don't touch `theme.css`. Redeclare the tokens you want
to change in your `_brand.css` `@theme` — because it loads last, it wins:

```css
/* _brand.css */
@theme {
  --text-base: clamp(1.125rem, 1.09rem + 0.18vw, 1.25rem);  /* bigger base */
  --text-2xl:  clamp(2.20rem, 1.80rem + 2.0vw, 3.20rem);    /* punchier ramp */
}
```

---

## 8. Global constants (`codey/globals.css`)

`globals.css` holds theme-**independent** brand values that must outrank even
`bespoke`, so they live *unlayered* (top of the cascade):

- **Fixed brand constants** — `--color-black`, `--color-white` (literal hex,
  first-paint safe).
- **Active/interaction colours** — `--color-active-1` … `--color-active-4` (a
  theme may override these within its scope).
- **Report status colours** — `--report-green/-orange/-red` and their `-bg`
  variants, for score/report components.

`globals.css` declares **no `@font-face`** and the package ships **no font files** —
typefaces are brand-specific and licence-bound, so they live in a project-owned
**brand typography sheet** (see §11.1) and in `head.php` for critical weights. The
package only *names* the expected families in the font tokens (`--font-body`,
`--font-body-medium`, `--font-head`, `--font-subhead`, `--font-italic` — each
with a paired `--*-weight`), each falling back to
`--font-fallback` (system UI) until your project supplies the faces.

---

## 9. Colour system — palettes + semantic themes

Colour is two layers: raw **palettes** (the ramp) and **semantic themes**
(meaning-based aliases over a palette). You select a theme with a body class.

> **Scale orientation: `0` = DARKEST → `9` = LIGHTEST.** "Least light → most
> light". Every palette, semantic map and component token in the system follows
> this. *Historical note:* the original hand-built palette ran the other way
> (0 = lightest, ending at 8). It was reversed by `new = 9 − old`, with the half
> steps following (`--color-65` → `--color-25`, `--color-75` → `--color-15`), so
> each alias kept the tone it always had and only the index changed. If you have
> CSS written against the old order, invert it the same way.

### 9.0 Generating a brand palette

> **Running the tool.** It ships at `scripts/brand-palette.cjs` and is wired to an
> npm script:
>
> ```bash
> npm run palette -- --dark "#…" --light "#…"
> npm run palette                          # no args: print every option
> ```
>
> (the `--` passes the flags through npm). Or call it directly:
> `node scripts/brand-palette.cjs --dark "#…" --light "#…"`.

Palettes are **generated**, not hand-picked, so the steps are perceptually even.
`scripts/brand-palette.cjs` interpolates in OKLCH between the two poles of a
spectrum and writes a project-owned stylesheet:

```bash
npm run palette -- \
  --dark "#0f151b" --light "#eef6fe" --mid "#1fa7f3" \
  --half 1.5,2.5 --scope ".theme-brand" \
  --out src/assets/css/_brand-palette.css
```

Zero dependencies (plain Node), and it **refuses to write into a `codey/` zone** —
the output is a project-owned brand artefact.

**Getting a rich centre.** Both poles of a brand spectrum are near-neutral
(C≈0.015), so a plain interpolation gives a flat, washed-out ramp. Three ways to
put colour back in the middle:

| Flag | Approach |
|---|---|
| `--mid <hex>` | **Three-point anchor** — interpolate dark→brand→light. Most faithful: you supply the real mid-tone. |
| `--cusp` | **Gamut-cusp riding** — push chroma to the maximum the gamut holds at each lightness. Richest displayable ramp. |
| `--mid-chroma <n>` | Absolute chroma target at the midpoint (simple bell curve). |

**Gamut.** `--gamut` defaults to **`p3`**, the native gamut of current displays;
clamping to sRGB needlessly desaturates them (on the codey ramp, P3 holds
C≈0.216 where sRGB caps at ≈0.167). Pass `--gamut srgb` only if you must stay in
the legacy gamut. Output is always clamped by *reducing chroma only*, preserving
L and H, so no step is left for the browser to clip unpredictably. Legacy
`hex`/`rgb()` values are still sRGB — it's `oklch()` being device-independent
that lets the wider gamut be addressed at all.

Half steps use the decimal-dropped naming: `--half 1.5,2.5` emits `--color-15`
and `--color-25`.

### 9.1 Palettes

The core ships a single palette; a project adds its own brand palette alongside it
(see §9.4).

- **`_codey.css`** (`.theme-codey`) — deep ocean blues, generated in OKLCH. A
  0–9 scale (`--color-0` **darkest** … `--color-9` **lightest**), plus half-steps
  (`--color-15`, `--color-25`), `--keycolor-*`, and first-paint
  `--color-background` / `--color-text` literals.

> The system previously bundled `_caramel` and a `_users` template palette; those
> have been removed to keep the core lean. `_codey` / `theme-codey` is now the
> single reference implementation you model your own brand palette on.

### 9.2 Semantic themes

`theme-codey.css` imports its palette and maps it to a **semantic vocabulary** —
the aliases the components actually consume, so a rule says `var(--link)` not
`var(--color-4)`. Key aliases:

```
--logo        --link         --hover        --cta-fill    --cta-text
--nav-text    --nav-social   --nav-item-bg  --nav-item-bg-current  --nav-item-bgPC
--blockquote-color  --blockquote-border     --shadow-color   --saturate
--color-button-bg   --color-button-text     --color-text-muted   --colour-hr
--color-grey  --color-light  --color-dark   --date-col    --product-card-bg
--color-background  --color-text
```

Each theme also re-applies `background-color`/`color` from the first paint so the
page is correct before CSS finishes loading.

### 9.3 Selecting a theme

The theme is a class on `<body>`. The plugin's layout shell sets it from a Kirby
`theme` field, falling back to the default flavour:

```php
<body class="<?= $page->theme()->or('theme-codey') ?>">
```

Add a `theme` field to your site/page blueprint returning `theme-codey` (or your
own brand theme, §9.4) to switch flavours per page, or hardcode the class.

### 9.4 Making your own brand palette

With the `_users` template removed, model your brand palette on the `_codey`
palette + `theme-codey` theme as the reference pair. All of this lives in
**project-owned** files (never in a synced zone), so it survives `composer update`:

1. Generate a project palette into `src/assets/css/_brand-palette.css` with
   `brand-palette.cjs` (§9.0), scoped to your brand class (`.theme-acme`). That
   gives you the `--color-0…9` scale (0 = darkest) plus any `--half` steps. Add
   `--keycolor-*` if you use them, and the first-paint `--color-background` /
   `--color-text` literals — mirror the structure of `codey/palettes/_codey.css`.
2. Add a semantic mapping (mirror `codey/themes/theme-codey.css`) so `--link`,
   `--hover`, `--nav-*`, `--cta-*`, etc. resolve for your brand.
3. Import both from `main.css` **after** `codey/index.css` (or from `_brand.css`),
   so they load last and win.
4. Set `class="theme-acme"` on `<body>` (via the Kirby `theme` field or hardcoded).

If you only need to *tweak* the shipped codey colours rather than define a new
brand, skip the new palette and just override the specific `--color-*` /
`--color-active-*` / semantic tokens in `_brand.css`.

---

## 10. Layout (`codey/lib/layout.css`)

The layout is a **two-axis frame** built from two non-competing grids that compose
because they never touch the same property.

**Row axis (vertical).** `<body>` is a grid with three rows —
`header / main / footer` — using `grid-template-rows: auto 1fr auto`, giving a
sticky footer (main fills the gap). `header` and `footer` cap to the content
measure and centre.

**Column axis (horizontal).** `.frame` (normally on `<main>`) is a named-column
grid:

```
[full-start] gutter [content-start] measure [content-end] gutter [full-end]
```

- `.frame > *` sits in the **content** track by default (framed).
- `.frame > .bleed` opts out to the **full** track (edge-to-edge).
- Legacy `.bleed-viewport` / `.grid-bleed` children are remapped to the full
  track automatically, so existing bleed markup keeps working.

**Two knobs** drive the frame, both overridable:

- `--frame-gutter` (default `--spacing-4-m`) — the side gutter.
- `--measure-page` (default `82rem`) — the max content width; header, footer, and
  main all cap to the same measure so they stay edge-aligned.

**Mode switch** — `data-reach` on the layout root:

| Mode | Meaning | Rule |
|---|---|---|
| `frame` | gutter present, single centred measure | none needed — this *is* the default |
| `spread` | the default, plus per-block `.bleed` opt-outs. The global default on `<main>`. | none needed |
| `bleed` | whole page edge-to-edge | `--frame-gutter-current: 0` |

Only `bleed` carries a rule. `frame` is the default behaviour and `spread` is
that default plus children opting out individually — the absence of rules for
those two is the design, not an omission.

**Vertical rhythm** — `data-pad` on the layout root, a 4-step scale:
`none` / `narrow` / `medium` / `large` set `--pad-block-start` / `--pad-block-end`
padding. `none` (alias `zero`) sets both to `0` — flush to header and footer.

**Nested containers** — `.track` uses `grid-template-columns: subgrid` to inherit
the page's column tracks while running its own rows; `.track.bleed` spans full.

---

## 10.1 Grids (`codey/lib/grid.css`)

Separate file, separate question. `layout.css` decides **where** a block sits on
the horizontal column axis; `grid.css` decides **how** that block divides its own
width. Column divisions play no part in framing, and framing plays no part in
column division — keeping them in two files is what keeps that true.

**The grid device is the theme class.** The layout field's `theme` attr writes a
class onto the row, and that class *is* the grid. There is no separate wrapper
class and the renderer adds nothing of its own (§15.4).

| Class | Geometry | Surface |
|---|---|---|
| `.grid-plain` | 12-col ≥60rem, no gap | none |
| `.grid-plain-padded` | 12-col ≥60rem, gap + block margin | none |
| `.grid-cards` | 12-col ≥60rem, gap + block margin | `--color-1` background, `--radius-lg`, `--spacing-6` padding |
| `.grid-bleed` | **auto-fit** tracks sized by `--min` | none |

The first three are one family — identical geometry, differing only in gap,
block margin and surface. Below `60rem` they collapse to a single column and
`--span` is ignored entirely; from `60rem` a `.column` spans its inline
`--span`.

`.grid-bleed` is a **different device, not a wider variant**. It runs
`repeat(auto-fit, minmax(var(--min, 16rem), 1fr))` and never consults
`--span` — the Panel's column widths genuinely don't apply to a bleed-viewport
row. Standalone it escapes its container with `100dvw` + a negative margin; as a
direct child of `.frame` that hack is neutralised in favour of clean track
placement (§10).

`.bleed-viewport` and `.bleed-viewport-clip` are the same escape hatch without the grid
(the latter adds `overflow-x: clip` for decoration that would otherwise widen the
document).

**`.grid-12`** is a plain 12-track utility grid for hand-written markup — the
core footer uses it. Same `.column` + `--span` contract, so columns behave
identically wherever they appear:

```html
<section class="grid-12">
  <div class="column" style="--span: 6">…</div>
  <div class="column" style="--span: 6">…</div>
</section>
```

---

## 11. Typography (`codey/lib/typography.css`)

Element-level defaults on the Utopia/Tailwind scale, imported into `layer(base)` so
utilities can still override them. Token-referencing only — semantic colours resolve
from the active theme.

- **`body`** — `--font-body` in `--color-text` on `--color-background`,
  `--leading-base`, `--text-base`, `geometricPrecision`, smooth scroll.
- **Headings** split by level: `h1`–`h3` use the display face (`--font-head` /
  `--weight-head`); `h4`–`h6` are *not* decorative — they step down to
  `--font-subhead` / `--weight-subhead` (typically a sans or serif at 500 variable /
  400 pre-weighted). Scale: `h1`=`--text-4xl`,
  `h2`=`--text-3xl`, `h3`=`--text-2xl`, `h4`=`--text-xl`, `h5`=`--text-lg`,
  `h6`=`--text-base`; `h1.super`=`--text-6xl`.
- **Heading step modifiers** — `h2.down-step`, `h2.down-step-x2`, `h2.up-step`
  nudge a heading up or down the scale without changing the tag.
- **Inline** — `strong/b/.font-medium` use `--font-body-medium` / `--weight-body-medium`
  (see the callout below); `em` uses a real italic face (`--font-italic`) rather
  than a synthetic slant; links use `--link` → `--hover`.
- **Mono (stylistic)** — `code`, `kbd`, `samp`, `pre` and the `.mono` helper use
  `--font-mono` / `--weight-mono`. This is a *typeface* choice — the monospace
  texture for code and technical strings — and has nothing to do with column
  alignment. The inline three stay at `1em` to match their host context.
- **Column data** — `.data` stays in the **body face** and switches only the
  numerals to a fixed advance via `font-variant-numeric: tabular-nums`, with
  tracking tokenised as `--data-tracking`. Columns align and live values stop
  jittering *without* importing the monospace texture. It inherits, so `.data`
  on a `<table>` covers every cell; compose with `.small` for the small step.
- **Helpers** — `.heads` and `.decor` opt into the display face (`--font-head`),
  `.headsans` forces the subhead face (`--font-subhead`), `.leading-tighter` tightens
  line-height, `.small` steps down to `--text-sm`.

> **`strong`/`b` stay in the body family.** They are *inline body markup* — a bold
> word inside a paragraph must not change typeface. So they use
> **`--font-body-medium`**, the body family's medium cut, never a heading face.
> Wiring them to `--font-subhead` (the h4–h6 subhead token) is a common mistake: it
> happens to look right when the subhead and body share a superfamily, and breaks
> the moment a brand's subhead face differs.

**Family + weight pairs.** Every face is a `--*-font` / `--*-weight` pair, because
that pair is the seam between the two ways a brand ships type:

| Strategy | Family | Weight |
|---|---|---|
| **Pre-weighted** (static cuts, one file each) | changes (`Gotham-Book` → `Gotham-Med`) | stays `400` |
| **Variable** (one family, `wght` axis) | stays the same | rises (`400` → `500/600`) |

Codey's defaults assume pre-weighted faces. A variable-font project sets
`--font-body-medium: var(--font-body)` and `--weight-body-medium: 500` instead — all in
the brand typography sheet, no vendored CSS edited.

> `typography.css` deliberately contains **no `@font-face`** *and no*
> `font-variation-settings`. The latter is an inherited property that overrides
> `font-weight` on descendants for variable fonts, which would silently defeat
> every weight token. Browsers map `font-weight` onto the `wght` axis anyway.
> The faces themselves are yours; see below.

### 11.1 Brand typography sheet (`_brand-typography.css`) — project-owned

All `@font-face` rules live **outside the package**, in a customizable sheet you
own. The package ships no fonts and no face declarations (typefaces are
brand-specific and licence-bound), so nothing here is ever overwritten by a sync.

Create `src/assets/css/_brand-typography.css` — a starter is provided at
`package/fonts/brand-typography.example.css`; copy it once (that folder is
guidance only and is **not** synced):

```css
/* Project-owned. Never place this inside a codey/ zone — those get wiped. */
@font-face {
  font-family: "Gotham-Med";
  src: url("../fonts/GothamHTF-Medium.woff2") format("woff2");
  font-weight: 400; font-style: normal; font-display: swap;
}
/* …one @font-face per weight/style you ship… */

/* Point the design-system tokens at your faces (or keep the defaults). */
@theme {
  --font-body: "Gotham-Book", var(--font-fallback);
  --font-head: "YourDisplay", var(--font-fallback);   /* h1–h3 */
  --font-subhead:  "Gotham-Med",  var(--font-fallback);   /* h4–h6, strong */
}
```

Load it **after** the codey core in `main.css` so its `@theme` wins:

```css
@import "./codey/index.css";        /* core (tier 1) */
@import "./_brand-typography.css";   /* faces + font-token overrides */
@import "./_brand.css";              /* the rest of your brand overrides (tier 2) */
```

**Critical faces are also declared in `head.php`.** Fonts needed for first paint
should be preloaded (or their `@font-face` inlined) in the Kirby head snippet, so
text doesn't flash the fallback while `main.css` loads:

```php
<?php /* src/site/snippets/head.php — project-owned */ ?>
<link rel="preload" as="font" type="font/woff2"
      href="<?= url('assets/fonts/GothamHTF-Medium.woff2') ?>" crossorigin>
<style>
  /* inline ONLY the critical face(s) — keep this tiny */
  @font-face {
    font-family: "Gotham-Med";
    src: url("<?= url('assets/fonts/GothamHTF-Medium.woff2') ?>") format("woff2");
    font-display: swap;
  }
</style>
```

Rule of thumb: **critical faces → preloaded/inlined in `head.php`; everything else
→ `_brand-typography.css`.** Both are project-owned; neither is ever synced. Until
you supply faces, every font token resolves to `--font-fallback` and the site
renders correctly in system UI.

---

## 12. Elements (`codey/lib/elements.css`)

Responsive media boxes that hold aspect ratio. `.img` and `.video` are ratio
containers driven by `--w` / `--h`:

```html
<div class="img" style="--w: 16; --h: 9">
  <img src="…" alt="…">
</div>
```

The inner `<img>` / `<iframe>` is absolutely positioned and `object-cover`;
`data-contain` on `.img` switches to `object-contain`. These use `@apply`, so they
resolve in your Tailwind context (which is why they live in the compiled bundle,
not a standalone file).

---

## 13. Per-template defaults (`codey/templates/`)

The core ships a tier-1 default for named templates, e.g. `note.css`:

```css
.note { max-width: 60ch; padding-block: var(--spacing-8); }
```

Your project's own `src/assets/css/templates/note.css` (tier 3, loaded later via
`css('@auto')`) overrides it for that template only. This is the mechanism for
template-scoped styling that doesn't leak globally.

---

## 14. Optional component seeds (`codey/lib/`)

Four optional files ship as **token seeds, not full components** — the generically
useful custom properties distilled from real components, with guidance comments.
You enable them (see §6/§9.4 for the *permanent* way, from `main.css`) and then
build your own markup on the tokens, so every project shares one vocabulary while
keeping its own HTML.

- **`form.css`** — `--form-radius`, `--input-border-color`, `--input-focus-color`,
  `--input-focus-ring`, `--input-padding`, `--btn-bg/-text/-radius/-transition`.
  Build `.your-form input { border-radius: var(--form-radius); … }` on top.
- **`accordion.css`** — `--accordion-duration(-fast)`, `--accordion-ease`,
  `--accordion-header-height`, `--accordion-gap`, `--accordion-fade-in/-out`. Pair
  with your own `details/summary` or Alpine disclosure.
- **`transitions.css`** — `--transition-fast/-base/-slow`, `--ease-standard/-out`,
  and a `--rise-from/--rise-to` pair seeding a fade-rise entrance.
- **`cards.css`** — `--card-bg`, `--card-radius`, `--card-gap`,
  `--card-pad-block/-inline`, `--card-check-color`, `--card-badge-color/-bg`.
  Palette references re-skin with the theme; structural literals (radius/gap) are
  intentionally fixed.

The point of seeds: you get consistent motion/spacing/colour vocabulary without the
system dictating your component markup. Override any literal in `_brand.css`.

---

## 15. The Kirby layer (plain site files)

The Kirby layer is ordinary Kirby source under `src/site/`, mirrored to
`build/site/` by the build. There is no plugin and no registration — every snippet,
blueprint and template resolves natively by its path, exactly like site files you
wrote yourself. The layout engine (the pieces that make Codey *Codey*) is grouped
under a `codey/` snippet namespace so it reads as core.

**How things resolve.** Path-addressed things live at their name:
`snippet('codey/frame')` → `src/site/snippets/codey/layout.php`;
`extends: fields/layout` → `src/site/blueprints/fields/layout.yml`. Name-addressed
things resolve against Kirby's flat namespaces natively: a layout field's
`fieldsets: [heading, image]` finds `src/site/blueprints/blocks/heading.yml`. As
plain files these Just Work — no registration, no namespace juggling (the very
thing the old plugin had to hand-register is now free).

**Customising.** You own the clone, so the direct route is to edit the file. If you
prefer to keep the `codey/` engine pristine for clean updates, Kirby still resolves
duplicates in your favour, so you can shadow a core snippet by adding your own —
e.g. a project `site/snippets/codey/header.php` — without touching the original.

Shipped files:

| Kind       | Names                                                                    |
|------------|--------------------------------------------------------------------------|
| Blueprints | `blocks/animations` · `heading` · `image` · `list` · `markdown` · `spacer` · `svg` · `text`; `files/blocks/svg`; `fields/cover` · `fields/layout`; `pages/{default,note,notes}` |
| Snippets   | core: `codey/card` · `codey/footer` · `codey/header` · `codey/layout` · `codey/layouts`; project starters: `head` · `head-hidden` · `cover-image` · `intro` · `prevnext` · `note-small` · `pagination` |
| Templates  | `default` · `home` · `note` · `notes`                                    |
| Controllers| `notes` · `note` (project starters)                                      |

The block names deliberately shadow Kirby's core blocks of the same name.

### 15.1 The layout shell — `snippet('codey/frame')`

A slot-based two-axis page shell. Templates call it like:

```php
<?php snippet('codey/frame', ['pad' => 'large', 'reach' => 'spread'], slots: true) ?>
  <?php slot() ?>
    …page content…
  <?php endslot() ?>
<?php endsnippet() ?>
```

Params:

- `head` — `'default'` or `'hidden'` (hidden = a noindex `<head>` variant).
- `pad` — `'none' | 'narrow' | 'medium' | 'large'` → `<main>` vertical rhythm
  (`data-pad`). `'zero'` is an accepted alias of `'none'`.
- `mode` — `'spread' | 'bleed' | 'frame'` → `.frame` track mode (`data-reach`),
  defaulting to `spread`. See §10 for what each means and why only `bleed`
  carries a rule.

Slots: the default slot becomes `<main>` content; an `intro` slot injects before
`<main>`. The shell sets `<body class="<?= $page->theme()->or('theme-codey') ?>">`
and declares Alpine `x-data="{ showNav: false }"` for the mobile nav. It renders the
**project's own** `head` snippet (you own `<head>`), then `codey/header`, your
content, then `codey/footer`.

### 15.2 Header — `snippet('codey/header')`

Structural only: logo (site title) + primary nav built from
`$site->children()->listed()` + a mobile toggle wired to the Alpine `showNav`
state. Decoration (logo SVG, social links) was stripped on extraction — add it back
by defining your own `codey/header` snippet at site level, which wins by name.

### 15.3 Footer — `snippet('codey/footer')`

Closes `<main>`, renders a `<footer>` using the `.grid-12` column system, and
emits the body-tail JS via `js(['@auto'])`. It's a generic scaffold — replace the
inner content by shadowing the snippet.

### 15.4 Layout-field renderer — `snippet('codey/frame'')`

Renders a Kirby **layout field** into the content track:

```php
<?php snippet('codey/frame'', ['field' => $page->layout()]) ?>
```

Each layout row becomes:

```html
<section class="{theme}" id="…">
  <div class="column" style="--span: 6"><div class="text">…blocks…</div></div>
</section>
```

The row's `theme` attr is emitted as the class, and **that class is the grid** —
`grid.css` defines the device on it (§10.1). The renderer adds no grid class of
its own, deliberately: choosing the device is the editor's decision in the Panel,
not the renderer's. Each column carries its Panel width as an inline `--span`,
which the 12-col devices consume at ≥60rem and `grid-bleed` ignores.

### 15.5 The layout blueprint field — `fields/layout`

Extend it in a page blueprint:

```yaml
fields:
  layout:                       # field key → $page->layout() in the template
    extends: fields/layout
```

> **How the reference resolves.** An `extends:` value is a **logical name** minus
> the `.yml`. `fields/layout` resolves to
> `src/site/blueprints/fields/layout.yml` (mirrored to `build/site/…`) — a plain
> file, no registration involved.
>
> - `layout` is a Kirby **field type**, not a core blueprint, so there is no
>   default `layout.yml` to collide with.
> - The field **key** (`layout`) is the method you call: `$page->layout()`. Rename
>   the key and you rename the method.
> - Override any key *after* `extends:` (a page-specific `label:`, different
>   `layouts:` presets, etc.) — later keys win.
> - It's part of the `codey` core; to change presets/fieldsets, edit it upstream
>   and pull, or override inline in the page blueprint after `extends:`.

It provides a layout field with column presets (`1/1`, `1/2,1/2`, `1/3,1/3,1/3`,
`1/4×4`, `2/3,1/3`, `1/3,2/3`), two per-row settings — a **Width** select
(`frame` / `spread` / `bleed`, §10.1) and a **Layout Theme** select
(`grid-plain` / `grid-plain-padded` / `grid-cards`) — and a generic fieldset
set (heading, text, image, line, list, markdown, quote, gallery, code).

### 15.6 Templates — `default` · `home` · `note` · `notes`

Registered, so they work out of the box, and superseded the moment you create
`site/templates/{name}.php` in your project. `default.php` shows the shell +
renderer together:

```php
<?php snippet('codey/frame', ['pad' => 'large'], slots: true) ?>
  <?php slot() ?>
    <?php snippet('codey/frame'', ['field' => $page->layout()]) ?>
  <?php endslot() ?>
<?php endsnippet() ?>
```

### 15.7 Wiring `<head>` — the load-order glue

Your project's `head` snippet is where tiers 1–3 get their precedence, via load
order:

```php
<?= css('assets/css/main.css') ?>   <!-- core (tier 1) + _brand.css (tier 2) -->
<?= css('@auto') ?>                  <!-- per-template tier-3 file, if it exists -->
```

`css('@auto')` loads `assets/css/templates/{template}.css` only when present, and
because it comes after `main.css`, the template file wins for that page.

---

## 16. The build pipeline

Codey requires only that `src/assets/css/main.css` compiles through Tailwind v4 and
that `src/` mirrors to `build/`, which Kirby serves.

- **`npm run build`** (no CodeKit): compiles `src/assets → build/assets` (Tailwind
  CSS only — CodeKit compiles the Alpine bundle) and mirrors `src/site → build/site`.
  `npm run css:watch` gives live CSS.
- **CodeKit**: point it at the project; it mirrors PHP and compiles CSS/JS on save,
  doing the same `src → build` work live.

Kirby loads from `build/` (its index path). Content lives under `build/content/`,
which is why `main.css`'s `@source` globs point there for class detection. `src/`
is the source of truth; the compiled `build/assets` + `build/site` are committed
(the server runs the repo), while Composer-installed dirs, rsynced binaries and
Kirby's generated data (media/cache/content) are gitignored.

---

## 17. Updating Codey, and sending fixes back

### There is no `git pull`

Earlier revisions of this guide said to `git pull` or cherry-pick from upstream.
**That does not work**, and it never did for any real project. The quickstart tells
you to `rm -rf .git && git init` on clone, so a project and this repo share **no
common ancestor** — merge, rebase, cherry-pick and subtree all have nothing to
anchor to. Verified: the SHA sets of the two repos intersect in zero commits.

### What works instead: patches

Git blobs are content-addressed, not history-addressed. An **unmodified** core file
therefore has the *same blob SHA* in both repos, so a diff built against it applies
cleanly across disjoint histories. That is the whole mechanism.

Fixes flow **one way — project → Codey**. The producing project owns the tooling
(see `scripts/codey-export.mjs` in the Rosie Boylan repo for the reference
implementation):

```bash
npm run codey:export              # dry run: what would travel, and does it apply
npm run codey:export -- go        # apply upstream, unstaged, for human review
npm run codey:export -- --drift   # how far have the core zones drifted?
```

It exports only the three core zones (`src/assets/css/codey/`,
`src/assets/js/codey/`, `src/site/snippets/codey/`), never commits, never pushes,
refuses deletions, and warns when a file has diverged upstream — because a clean
apply is not necessarily a correct apply.

To pull a newer Codey *into* an existing project, diff the core zones by hand and
copy across; there is deliberately no automated import. If you need many live sites
updating in place, that is the plugin-promotion path in
[ARCHITECTURE.md](ARCHITECTURE.md).

### Why this stays clean

You edit `brand/` and your own templates; `codey/` holds rules only, no brand
values. So core stays byte-identical to upstream, every core blob keeps matching,
and patches keep applying. The moment a project edits a core file to change a
*value*, that guarantee is gone — put the value in `brand/` instead.

### 17.1 Migrating to 2.0 (breaking)

**The palette scale was reversed to `0` = darkest → `9` = lightest.** Every
`--color-N` now means a different tone, so any project CSS written against the
old order must be inverted:

| Old | New | Notes |
|---|---|---|
| `--color-N` | `--color-(9−N)` | e.g. `--color-3` → `--color-6` |
| `--color-65` | `--color-25` | half steps moved to the dark end |
| `--color-75` | `--color-15` | |
| `--keycolor-5…8` | `--keycolor-4…1` | same rule |

Semantic aliases (`--link`, `--color-text`, `--nav-item-bg`, …) were remapped
inside the package, so **if you only ever used semantic tokens, nothing changes**.
Only direct `--color-N` references in project CSS need attention.

Also in 2.0:

- **`strong`/`b` now use `--font-body-medium`** (the body family's medium cut), not
  `--font-subhead`. If you relied on bold body copy rendering in the subhead face,
  set `--font-body-medium: var(--font-subhead)` in `_brand-typography.css`.
- **`.data` no longer sets a monospace face.** It stays in the body face with
  tabular figures. For the old behaviour use `.mono .data` together, or set
  `--font-body-medium`/`--font-mono` to taste.
- **`font-variation-settings` was removed** from the typographic base — it
  overrode `font-weight` on descendants and broke variable-font weighting.
- **New tokens:** `--weight-body`, `--weight-body-medium`, `--weight-head`,
  `--weight-subhead`, `--font-mono`, `--weight-mono`, `--data-tracking`.
- **Palettes are generated** — `_users.css` is gone; use `brand-palette.cjs`
  (§9.0) to produce a project palette instead.

---

## 18. Fresh-project checklist

1. `git clone <codey-repo-url> my-site && cd my-site`.
2. `cd build && composer install && cd ..` (Kirby + PHP deps).
3. `npm install && npm run build` (compiles `src/assets → build/assets`, mirrors
   `src/site → build/site`).
4. `npm run serve`, create the Panel admin at `/panel`.
5. Customise `src/assets/css/_brand.css` — `@theme` token overrides (type rescale,
   colours). `main.css` is already wired.
6. Generate a palette: `npm run palette -- --dark … --light … --mid …`, then
   uncomment its import in `_brand.css`.
7. Supply real font files into `src/assets/fonts/` and point the font tokens at
   them from `_brand.css`.
8. Pick/build a colour theme; set the `.theme-*` class on `<body>` (the starter
   `codey/layout` uses `$page->theme()->or('theme-codey')`).
9. Edit the starter `head` snippet if needed (it already links
   `assets/css/main.css` + `@auto` + Alpine).
10. Build pages from `codey/layout` + `codey/layouts`; add
    `layout: { extends: fields/layout }` to your page blueprint.
11. Deploy `build/` (its `.htaccess` + `index.php` are the web root).

---

## Appendix A — Token quick reference

**Type scale:** `--text-xs · sm · base · lg · xl · 2xl · 3xl · 4xl · 5xl · 6xl · 7xl · 8xl` (fluid `clamp()`).

**Spacing scale:** `--spacing-4xs · 1 … 17` plus pair steps `--spacing-3-s · 4-m · 5-l · 6-xl · 7-2xl · 8-3xl · 9-4xl … 14-9xl`.

**Line-heights:** `--leading-tight · mid · mad · big · tighter · base · head`.

**Fonts (family + weight pairs):** `--font-body` (Gotham-Book, body) · `--font-body-medium` (Gotham-Med, `strong`/`b`) · `--font-head` (Gradual, h1–h3) · `--font-subhead` (Gotham-Med, h4–h6) · `--font-italic` (Gotham-Ital, `em`) · `--font-fallback`.
**Mono (stylistic):** `--font-mono` (defaults to `--mono-fallback`, the system mono stack — works with no face supplied) · `--mono-fallback`.
**Column data:** `--data-tracking` (letter-spacing for `.data`; `0em` default). `.data` keeps the body face and uses tabular figures.
**Weights:** `--weight-body` · `--weight-body-medium` · `--weight-head` · `--weight-subhead` · `--weight-mono` — all `400` by default (pre-weighted faces); variable-font projects raise `--weight-body-medium`/`--weight-subhead` to `500`.

**Effect tokens:** `--blur` · `--glass-transparency` · `--radius-lg` · `--border-radius`.

**Palette scale (per theme):** `--color-0 … 9` — **0 = darkest → 9 = lightest** (+ half steps, e.g. `--color-15`, `--color-25`), `--keycolor-1…4`, `--color-active-1…4`.

**Semantic aliases:** `--link · --hover · --logo · --cta-fill · --cta-text · --nav-text · --nav-social · --nav-item-bg · --nav-item-bg-current · --blockquote-color · --blockquote-border · --shadow-color · --saturate · --color-button-bg · --color-button-text · --color-text-muted · --colour-hr · --color-background · --color-text`.

**Global constants:** `--color-black · --color-white · --report-{green,orange,red}(-bg)`.

## Appendix B — Layout attributes

| Attribute / class      | Where           | Effect                                             |
|------------------------|-----------------|----------------------------------------------------|
| `.frame`              | usually `<main>`| the track-axis column grid (content/full)          |
| `data-reach="frame\|spread\|bleed"` | `.frame` | page mode; only `bleed` has a rule (gutter → 0) |
| `data-pad="none\|narrow\|medium\|large"` | `.frame` | vertical rhythm on `<main>` (`none` = 0)  |
| `.bleed`               | `.frame` child | opt a child out to the full (edge-to-edge) track   |
| `.grid-plain(-padded)` / `.grid-cards` | layout row | 12-col grid device (grid.css)   |
| `.grid-bleed`     | layout row      | auto-fit grid, edge to edge — ignores `--span`  |
| `.track` / `.track.bleed` | nested        | subgrid container inheriting page columns          |
| `.grid-12`         | section         | 12-track content grid (pairs with layout renderer) |
| `.column` + `--span`| `.grid-12` child | span N of 12 tracks (stacks below 60rem)     |
| `--frame-gutter`       | `:root`/override| side gutter width                                  |
| `--measure-page`     | `:root`/override| max content width (default 82rem)                  |
