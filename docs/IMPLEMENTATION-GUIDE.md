# Implementing the Codey Design System in a Fresh Kirby Project

A practical, element-by-element guide to standing up a new Kirby CMS site on the
Codey design system (`ianhobbsmedia/codey-design-system`). It covers installation,
the sync/override model, and every part of the system — tokens, colour, layout,
typography, elements, component seeds, and the Kirby plugin — with the reasoning
behind each so you know not just *what* to type but *why*.

> **Version note.** Written against Codey `v2.0.0`. The package is published on
> Packagist and pulled in via Composer; a post-install script copies its source
> into your project's `src/`.

---

## 1. The mental model (read this first)

Codey is **not** a dependency you reference in place from `vendor/`. It is a
*versioned source kit* that lands as plain files under your project's `src/`, so
your own build pipeline (Tailwind CLI, CodeKit, or Vite) compiles it exactly like
files you wrote yourself. Two channels do two jobs:

- **Composer** fetches and semver-pins the package into `vendor/` — the *version*
  channel (`composer.lock` records the exact release).
- **`codey-sync`** (a script Composer runs on install) copies the package source
  from `vendor/` into four fixed zones in `src/` — the *placement* channel.

Everything the system ships points **downward only**: a rule references a token
below it, never up into your project. That inversion — your project depends on the
system, the system depends on nothing project-specific — is the whole point, and
it's what lets you re-skin by overriding tokens instead of editing core files.

The layer stack, top (yours) to bottom (foundational):

```
your project      main.css, _brand.css, templates, snippets   ← you author only this
Kirby plugin      layout shell, header/footer, layout renderer
CSS core          layout frame, typography, elements
Colour system     palettes + semantic themes
Tokens            @theme Utopia type/space, :root globals
```

---

## 2. Prerequisites

- **Kirby 4+** project (the plugin uses slots and `$field->toLayouts()`).
- **Composer** — the version + install channel.
- **Node 18+ and Tailwind CSS v4** — Codey's tokens are authored as Tailwind v4
  `@theme` blocks. v4 is required (the `@theme`, `@source`, `@layer`, and
  `@import "tailwindcss"` syntax is v4-only).
- A **`src/ → build/` convention** — you author in `src/`, and a build step
  mirrors/compiles into `build/`, which is what Kirby actually serves. Codey is
  built around this split; CodeKit, a Tailwind CLI script, or Vite all work.

---

## 3. Installation

### 3.1 Add the consumer `composer.json`

At your project root:

```json
{
  "name": "you/your-site",
  "description": "Kirby site consuming the Codey design system.",
  "type": "project",
  "license": "proprietary",
  "require": {
    "getkirby/cms": "^4.0",
    "ianhobbsmedia/codey-design-system": "*"
  },
  "scripts": {
    "codey-sync": "vendor/bin/codey-sync.cjs",
    "post-install-cmd": [ "@codey-sync" ],
    "post-update-cmd": [ "@codey-sync" ]
  }
}
```

Notes on the choices here:

- **`"*"`** tracks the latest published release. Pin to `"~1.0"` once you want to
  freeze a major line.
- **`vendor/bin/codey-sync.cjs`** — both tools are declared in the package's
  `composer.json` `bin`, so Composer writes a small proxy into `vendor/bin/` on
  install and marks the target executable. That's the conventional short path;
  the real file stays at
  `vendor/ianhobbsmedia/codey-design-system/package/scripts/`. Bins are created
  *before* `post-install-cmd` runs, so referencing the proxy here is safe.
- The **script name ends in `.cjs`**, not `.js`. The sync script is CommonJS; the
  `.cjs` extension forces Node to treat it as CommonJS even inside a project whose
  `package.json` sets `"type": "module"`. (This is a real gotcha — an ESM project
  will crash a `.js` copy with `require is not defined`.)
- `post-install-cmd` and `post-update-cmd` both run the sync, so the synced files
  are refreshed on every `composer install` and `composer update`.

### 3.2 Add the front-end toolchain (npm)

Composer can't fetch npm packages, so Tailwind (and Alpine, used by the header's
mobile toggle) live beside it:

```json
{
  "name": "your-site",
  "private": true,
  "type": "module",
  "scripts": {
    "build:css": "tailwindcss -i ./src/assets/css/main.css -o ./build/assets/css/main.css",
    "watch:css": "tailwindcss -i ./src/assets/css/main.css -o ./build/assets/css/main.css --watch"
  },
  "devDependencies": {
    "@tailwindcss/cli": "^4.0.0",
    "tailwindcss": "^4.0.0"
  },
  "dependencies": {
    "alpinejs": "^3.14.0"
  }
}
```

### 3.3 Install and see what lands

```bash
composer install     # fetches the package → vendor/, then post-install-cmd runs codey-sync
npm install          # Tailwind + Alpine
npm run build:css     # compiles src/assets/css/main.css → build/assets/css/main.css
```

`codey-sync` writes **only** these four zones, plus a `src/.codey-version` stamp:

| Synced zone (vendored)      | From package    | Contents                                   |
|-----------------------------|-----------------|--------------------------------------------|
| `src/assets/css/codey/`     | `package/css/`  | tokens, palettes, themes, `lib/`, `index.css` |
| `src/assets/js/codey/`      | `package/js/`   | shared JS (e.g. Alpine init) |
| `src/site/snippets/codey/`  | `package/kirby/snippets/`   | vanilla snippets (Kirby auto-discovers) |
| `src/site/blueprints/codey/`| `package/kirby/blueprints/` | layout field (`codey/fields/layout`) |

**Clobber-safety contract:** the script *wipes and re-copies only those exact dest
paths*. Everything else in `src/` — your `main.css`, `_brand.css`, templates,
snippets — is project-owned and never touched. Overwriting a project file is
structurally impossible because the write set is a fixed, declared list.

### 3.4 Git hygiene

Treat the synced zones like `vendor/` — they're reproducible from `composer.lock`:

```gitignore
# Codey — vendored, synced on install. Reproducible via committed composer.lock.
# NEVER hand-edit these; overrides live in your project-owned brand layer.
src/assets/css/codey/
src/assets/js/codey/
src/site/snippets/codey/
src/site/blueprints/codey/
src/.codey-version
```

**Commit `composer.json` and `composer.lock`.** The lock file is what pins the
exact Codey version, so a fresh checkout + `composer install` reproduces the same
`codey/` files deterministically. (If you'd rather have the vendored folder in git
for offline builds and reviewable upgrade diffs, you can commit it instead — just
never hand-edit inside it. Both models are valid; ignoring is the default.)

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

On the **Kirby side**, the core snippets and blueprint land as **vanilla files**
under `site/snippets/codey/` and `site/blueprints/codey/`, which Kirby
auto-discovers (`snippet('codey/layout')`, `extends: codey/fields/layout`) — no
plugin registration. They live in synced (vendored) zones, so you customise by
wrapping or calling your own snippet, not by editing the vendored PHP in place.

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

**Augments** — extra tokens layered on top: `--padding`, a set of `--leading-*`
line-heights (`tight`, `mid`, `mad`, `big`, `tighter`, `base`, `head`), the font
stacks (`--body-font`, `--bodymed-font`, `--head-font`, `--med-font`, `--ital-font`,
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
- **`--note-width`** — the default text measure (47rem).

`globals.css` declares **no `@font-face`** and the package ships **no font files** —
typefaces are brand-specific and licence-bound, so they live in a project-owned
**brand typography sheet** (see §11.1) and in `head.php` for critical weights. The
package only *names* the expected families in the font tokens (`--body-font`,
`--bodymed-font`, `--head-font`, `--med-font`, `--ital-font` — each
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

> **Finding the tool.** It is declared in the package's `composer.json` `bin`, so
> Composer proxies it to **`vendor/bin/brand-palette.cjs`** on install — the
> conventional location for a package's executables. It is *not* synced into
> `src/`; only its output is. (`vendor/` is gitignored, so editor search skips it.)
>
> ```bash
> vendor/bin/brand-palette.cjs --dark "#…" --light "#…"
> vendor/bin/brand-palette.cjs            # no args: print every option
> ```
>
> Optionally alias it: `"codey:palette": "vendor/bin/brand-palette.cjs"`, then
> `npm run codey:palette -- --dark "#…"` (the `--` passes the flags through).

Palettes are **generated**, not hand-picked, so the steps are perceptually even.
`package/scripts/brand-palette.cjs` interpolates in OKLCH between the two poles
of a spectrum and writes a project-owned stylesheet:

```bash
vendor/bin/brand-palette.cjs \
  --dark "#0f151b" --light "#eef6fe" --mid "#1fa7f3" \
  --half 1.5,2.5 --scope ".theme-brand" \
  --out src/assets/css/_brand-palette.css
```

Zero dependencies (plain Node, like `codey-sync`), and it **refuses to write
into a `codey/` zone** — the output is a project-owned brand artefact.

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

**Skeleton axis (vertical).** `<body>` is a grid with three rows —
`header / main / footer` — using `grid-template-rows: auto 1fr auto`, giving a
sticky footer (main fills the gap). `header` and `footer` cap to the content
measure and centre.

**Track axis (horizontal).** `.layout` (normally on `<main>`) is a named-column
grid:

```
[full-start] gutter [content-start] measure [content-end] gutter [full-end]
```

- `.layout > *` sits in the **content** track by default (framed).
- `.layout > .bleed` opts out to the **full** track (edge-to-edge).
- Legacy `.full-bleed` / `.full-bleed-grid` children are remapped to the full
  track automatically, so existing bleed markup keeps working.

**Two knobs** drive the frame, both overridable:

- `--frame-gutter` (default `--spacing-4-m`) — the side gutter.
- `--layout-measure` (default `82rem`) — the max content width; header, footer, and
  main all cap to the same measure so they stay edge-aligned.

**Mode switch** — `data-layout` on the layout root:

- `data-layout="bleed"` sets the gutter to 0 (full-width content).
- (The plugin also accepts `spread` / `frame` values you can style.)

**Vertical rhythm** — `data-pad` on the layout root, a 3-step scale:
`narrow` / `medium` / `large` set `--main-pt` / `--main-pb` padding.

**Nested containers** — `.track` uses `grid-template-columns: subgrid` to inherit
the page's column tracks while running its own rows; `.track.bleed` spans full.

**Content grid** — `.blocks-grid` is the generic 12-track grid that pairs with the
Kirby layout renderer. A `.column` spans `--columns` tracks (set inline), and
collapses to full width below `60rem`:

```html
<section class="blocks-grid">
  <div class="column" style="--columns: 6">…</div>
  <div class="column" style="--columns: 6">…</div>
</section>
```

---

## 11. Typography (`codey/lib/typography.css`)

Element-level defaults on the Utopia/Tailwind scale, imported into `layer(base)` so
utilities can still override them. Token-referencing only — semantic colours resolve
from the active theme.

- **`body`** — `--body-font` in `--color-text` on `--color-background`,
  `--leading-base`, `--text-base`, `geometricPrecision`, smooth scroll.
- **Headings** split by level: `h1`–`h3` use the display face (`--head-font` /
  `--head-weight`); `h4`–`h6` are *not* decorative — they step down to
  `--med-font` / `--med-weight` (typically a sans or serif at 500 variable /
  400 pre-weighted). Scale: `h1`=`--text-4xl`,
  `h2`=`--text-3xl`, `h3`=`--text-2xl`, `h4`=`--text-xl`, `h5`=`--text-lg`,
  `h6`=`--text-base`; `h1.super`=`--text-6xl`.
- **Heading step modifiers** — `h2.down-step`, `h2.down-step-x2`, `h2.up-step`
  nudge a heading up or down the scale without changing the tag.
- **Inline** — `strong/b/.font-medium` use `--bodymed-font` / `--bodymed-weight`
  (see the callout below); `em` uses a real italic face (`--ital-font`) rather
  than a synthetic slant; links use `--link` → `--hover`.
- **Mono (stylistic)** — `code`, `kbd`, `samp`, `pre` and the `.mono` helper use
  `--mono-font` / `--mono-weight`. This is a *typeface* choice — the monospace
  texture for code and technical strings — and has nothing to do with column
  alignment. The inline three stay at `1em` to match their host context.
- **Column data** — `.data` stays in the **body face** and switches only the
  numerals to a fixed advance via `font-variant-numeric: tabular-nums`, with
  tracking tokenised as `--data-tracking`. Columns align and live values stop
  jittering *without* importing the monospace texture. It inherits, so `.data`
  on a `<table>` covers every cell; compose with `.small` for the small step.
- **Helpers** — `.heads` and `.decor` opt into the display face (`--head-font`),
  `.headsans` forces the subhead face (`--med-font`), `.leading-tighter` tightens
  line-height, `.small` steps down to `--text-sm`.

> **`strong`/`b` stay in the body family.** They are *inline body markup* — a bold
> word inside a paragraph must not change typeface. So they use
> **`--bodymed-font`**, the body family's medium cut, never a heading face.
> Wiring them to `--med-font` (the h4–h6 subhead token) is a common mistake: it
> happens to look right when the subhead and body share a superfamily, and breaks
> the moment a brand's subhead face differs.

**Family + weight pairs.** Every face is a `--*-font` / `--*-weight` pair, because
that pair is the seam between the two ways a brand ships type:

| Strategy | Family | Weight |
|---|---|---|
| **Pre-weighted** (static cuts, one file each) | changes (`Gotham-Book` → `Gotham-Med`) | stays `400` |
| **Variable** (one family, `wght` axis) | stays the same | rises (`400` → `500/600`) |

Codey's defaults assume pre-weighted faces. A variable-font project sets
`--bodymed-font: var(--body-font)` and `--bodymed-weight: 500` instead — all in
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
  --body-font: "Gotham-Book", var(--font-fallback);
  --head-font: "YourDisplay", var(--font-fallback);   /* h1–h3 */
  --med-font:  "Gotham-Med",  var(--font-fallback);   /* h4–h6, strong */
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

## 15. The Kirby snippets + blueprint (`codey/`)

The Kirby layer ships as **vanilla files** — no `Kirby::plugin()` registration.
Snippets sync to `site/snippets/codey/` and the layout field to
`site/blueprints/codey/`, where Kirby auto-discovers them (`snippet('codey/layout')`,
`extends: codey/fields/layout`). They live in synced (vendored) zones, so you
customise by wrapping a snippet or overriding CSS/tokens rather than editing them
in place.

### 15.1 The layout shell — `snippet('codey/layout')`

A slot-based two-axis page shell. Templates call it like:

```php
<?php snippet('codey/layout', ['pad' => 'large', 'mode' => 'spread'], slots: true) ?>
  <?php slot() ?>
    …page content…
  <?php endslot() ?>
<?php endsnippet() ?>
```

Params:

- `head` — `'default'` or `'hidden'` (hidden = a noindex `<head>` variant).
- `pad` — `'narrow' | 'medium' | 'large'` → `<main>` vertical rhythm (`data-pad`).
- `mode` — `'spread' | 'bleed' | 'frame'` → `.layout` track mode (`data-layout`).

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

Closes `<main>`, renders a `<footer>` using the `.blocks-grid` column system, and
emits the body-tail JS via `js(['@auto'])`. It's a generic scaffold — replace the
inner content by shadowing the snippet.

### 15.4 Layout-field renderer — `snippet('codey/layouts')`

Renders a Kirby **layout field** into the content track:

```php
<?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
```

Each layout row becomes a `<section class="blocks-grid {theme}">`; each column
becomes `<div class="column" style="--columns: {span}">` wrapping a `.text` block
region. The row's `theme` attr (from the blueprint) lands as a class so you can
style the `plain-blocks` / `plain-blocks-padded` / `card-blocks` variants.

### 15.5 The layout blueprint field — `codey/fields/layout`

Extend it in a page blueprint:

```yaml
fields:
  layout:                       # field key → $page->layout() in the template
    extends: codey/fields/layout
```

> **Production note — how the reference resolves** (verified against Kirby core,
> `Blueprint::find()`). An `extends:` value is a path **relative to
> `site/blueprints/`, minus the `.yml`** — Kirby builds
> `root('blueprints') . '/' . $name . '.yml'`. So `codey/fields/layout` →
> `site/blueprints/codey/fields/layout.yml` (the synced zone), mirrored to
> `build/` for the running site. It is **not** `fields/codey-layout` — that name
> does not exist and throws `blueprint.notFound`.
>
> - `layout` is a Kirby **field type**, not a core blueprint, so there is no
>   default `layout.yml` to collide with; the `codey/` namespace keeps it unique.
> - The field **key** (`layout`) is the method you call: `$page->layout()`. Rename
>   the key and you rename the method.
> - Override any key *after* `extends:` (a page-specific `label:`, different
>   `layouts:` presets, etc.) — later keys win.
> - The blueprint lives in a synced (vendored) zone, so don't edit it in the
>   project. Change presets/fieldsets upstream in the package, or override them
>   inline in the page blueprint after `extends:`.

It provides a layout field with column presets (`1/1`, `1/2,1/2`, `1/3,1/3,1/3`,
`1/4×4`, `2/3,1/3`, `1/3,2/3`), a per-row **Layout Theme** select
(`plain-blocks` / `plain-blocks-padded` / `card-blocks`), and a generic fieldset
set (heading, text, image, line, list, markdown, quote, gallery, code).

### 15.6 Example template — `templates/default.php`

Shipped as an example and **not registered** (you own your `default.php`). It shows
the shell + renderer together:

```php
<?php snippet('codey/layout', ['pad' => 'large'], slots: true) ?>
  <?php slot() ?>
    <?php snippet('codey/layouts', ['field' => $page->layout()]) ?>
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

Codey is build-agnostic — it only requires that `src/assets/css/main.css` compiles
through Tailwind v4 and that `src/` mirrors to `build/`, which Kirby serves.

- **Tailwind CLI** (simplest): `npm run build:css` →
  `build/assets/css/main.css`.
- **CodeKit**: point it at `src/`, output to `build/`; it mirrors PHP and compiles
  CSS/JS. The synced snippets/blueprints at
  `src/site/{snippets,blueprints}/codey/` mirror to `build/site/…`.
- **Vite / custom (e.g. a `build.mjs`)**: compile `main.css`, bundle JS, copy
  images — any pipeline works as long as the `src → build` contract holds.

Kirby loads from `build/` (that's your document root / index path). Content lives
under `build/content/`, which is why `main.css`'s `@source` globs point there for
class detection.

---

## 17. Upgrading

```bash
composer update ianhobbsmedia/codey-design-system
# post-update-cmd re-runs codey-sync automatically
npm run build:css
```

The synced zones are wiped and re-copied to the new version; your project-owned
tiers (`main.css`, `_brand.css`, template files, shadowed snippets) are untouched
and continue to win. If you committed the vendored folder, review the diff; if you
gitignored it, the new version is pinned in the updated `composer.lock` — commit
that.

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

- **`strong`/`b` now use `--bodymed-font`** (the body family's medium cut), not
  `--med-font`. If you relied on bold body copy rendering in the subhead face,
  set `--bodymed-font: var(--med-font)` in `_brand-typography.css`.
- **`.data` no longer sets a monospace face.** It stays in the body face with
  tabular figures. For the old behaviour use `.mono .data` together, or set
  `--bodymed-font`/`--mono-font` to taste.
- **`font-variation-settings` was removed** from the typographic base — it
  overrode `font-weight` on descendants and broke variable-font weighting.
- **New tokens:** `--body-weight`, `--bodymed-weight`, `--head-weight`,
  `--med-weight`, `--mono-font`, `--mono-weight`, `--data-tracking`.
- **Palettes are generated** — `_users.css` is gone; use `brand-palette.cjs`
  (§9.0) to produce a project palette instead.

---

## 18. Fresh-project checklist

1. `composer.json` with the Codey require + `codey-sync` scripts (`.cjs` path,
   including `/package/`).
2. `package.json` with Tailwind v4 + Alpine and a `build:css` script.
3. `composer install` → `npm install` → confirm the four zones synced.
4. Gitignore the four zones + `.codey-version`; commit `composer.lock`.
5. Author `src/assets/css/main.css` — `@import "tailwindcss"`, `@layer` order,
   `@source` globs, `@import "./codey/index.css"`, then `@import "./_brand.css"`.
6. Author `src/assets/css/_brand.css` — `@theme` token overrides (type rescale,
   colours).
7. Supply real font files (see §8 caveat) into the package `fonts/` or point the
   font tokens at your own.
8. Pick/build a colour theme; set the `.theme-*` class on `<body>` (via a Kirby
   `theme` field or hardcoded).
9. Author your `head` snippet with `css('assets/css/main.css')` + `css('@auto')`.
10. Author `default.php` using `codey/layout` + `codey/layouts`; add
    `layout: { extends: codey/fields/layout }` to your page blueprint.
11. `npm run build:css`, point Kirby at `build/`, run.

---

## Appendix A — Token quick reference

**Type scale:** `--text-xs · sm · base · lg · xl · 2xl · 3xl · 4xl · 5xl · 6xl · 7xl · 8xl` (fluid `clamp()`).

**Spacing scale:** `--spacing-4xs · 1 … 17` plus pair steps `--spacing-3-s · 4-m · 5-l · 6-xl · 7-2xl · 8-3xl · 9-4xl … 14-9xl`.

**Line-heights:** `--leading-tight · mid · mad · big · tighter · base · head`.

**Fonts (family + weight pairs):** `--body-font` (Gotham-Book, body) · `--bodymed-font` (Gotham-Med, `strong`/`b`) · `--head-font` (Gradual, h1–h3) · `--med-font` (Gotham-Med, h4–h6) · `--ital-font` (Gotham-Ital, `em`) · `--font-fallback`.
**Mono (stylistic):** `--mono-font` (defaults to `--mono-fallback`, the system mono stack — works with no face supplied) · `--mono-fallback`.
**Column data:** `--data-tracking` (letter-spacing for `.data`; `0em` default). `.data` keeps the body face and uses tabular figures.
**Weights:** `--body-weight` · `--bodymed-weight` · `--head-weight` · `--med-weight` · `--mono-weight` — all `400` by default (pre-weighted faces); variable-font projects raise `--bodymed-weight`/`--med-weight` to `500`.

**Effect tokens:** `--blur` · `--glass-transparency` · `--radius-lg` · `--border-radius` · `--padding`.

**Palette scale (per theme):** `--color-0 … 9` — **0 = darkest → 9 = lightest** (+ half steps, e.g. `--color-15`, `--color-25`), `--keycolor-1…4`, `--color-active-1…4`.

**Semantic aliases:** `--link · --hover · --logo · --cta-fill · --cta-text · --nav-text · --nav-social · --nav-item-bg · --nav-item-bg-current · --blockquote-color · --blockquote-border · --shadow-color · --saturate · --color-button-bg · --color-button-text · --color-text-muted · --colour-hr · --color-background · --color-text`.

**Global constants:** `--color-black · --color-white · --report-{green,orange,red}(-bg) · --note-width`.

## Appendix B — Layout attributes

| Attribute / class      | Where           | Effect                                             |
|------------------------|-----------------|----------------------------------------------------|
| `.layout`              | usually `<main>`| the track-axis column grid (content/full)          |
| `data-layout="bleed"`  | `.layout`       | gutter → 0 (full-width content)                    |
| `data-pad="narrow\|medium\|large"` | `.layout` | vertical rhythm on `<main>`               |
| `.bleed`               | `.layout` child | opt a child out to the full (edge-to-edge) track   |
| `.track` / `.track.bleed` | nested        | subgrid container inheriting page columns          |
| `.blocks-grid`         | section         | 12-track content grid (pairs with layout renderer) |
| `.column` + `--columns`| `.blocks-grid` child | span N of 12 tracks (stacks below 60rem)     |
| `--frame-gutter`       | `:root`/override| side gutter width                                  |
| `--layout-measure`     | `:root`/override| max content width (default 82rem)                  |
