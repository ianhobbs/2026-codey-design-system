# Implementing the Codey Design System in a Fresh Kirby Project

A practical, element-by-element guide to standing up a new Kirby CMS site on the
Codey design system (`ianhobbsmedia/codey-design-system`). It covers installation,
the sync/override model, and every part of the system — tokens, colour, layout,
typography, elements, component seeds, and the Kirby plugin — with the reasoning
behind each so you know not just *what* to type but *why*.

> **Version note.** Written against Codey `v1.0.3`. The package is published on
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
  from `vendor/` into three fixed zones in `src/` — the *placement* channel.

Everything the system ships points **downward only**: a rule references a token
below it, never up into your project. That inversion — your project depends on the
system, the system depends on nothing project-specific — is the whole point, and
it's what lets you re-skin by overriding tokens instead of editing core files.

The layer stack, top (yours) to bottom (foundational):

```
your project      main.css, brand.css, templates, snippets   ← you author only this
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
    "codey-sync": "node vendor/ianhobbsmedia/codey-design-system/package/scripts/codey-sync.cjs",
    "post-install-cmd": [ "@codey-sync" ],
    "post-update-cmd": [ "@codey-sync" ]
  }
}
```

Notes on the choices here:

- **`"*"`** tracks the latest published release. Pin to `"~1.0"` once you want to
  freeze a major line.
- The **script path ends in `.cjs`**, not `.js`. The sync script is CommonJS; the
  `.cjs` extension forces Node to treat it as CommonJS even inside a project whose
  `package.json` sets `"type": "module"`. (This is a real gotcha — an ESM project
  will crash a `.js` copy with `require is not defined`.)
- The path includes **`/package/`** — the payload lives under `package/` in the
  repo, and Composer installs the whole repo into `vendor/…/`, so the script sits
  at `vendor/ianhobbsmedia/codey-design-system/package/scripts/codey-sync.cjs`.
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

`codey-sync` writes **only** these three zones, plus a `src/.codey-version` stamp:

| Synced zone (vendored)      | From package    | Contents                                   |
|-----------------------------|-----------------|--------------------------------------------|
| `src/assets/css/codey/`     | `package/css/`  | tokens, palettes, themes, `lib/`, `index.css` |
| `src/site/plugins/codey/`   | `package/kirby/`| plugin: snippets, blueprint field, `index.php` |
| `src/assets/fonts/codey/`   | `package/fonts/`| core font files (see the font caveat, §9)  |

**Clobber-safety contract:** the script *wipes and re-copies only those exact dest
paths*. Everything else in `src/` — your `main.css`, `brand.css`, templates,
snippets — is project-owned and never touched. Overwriting a project file is
structurally impossible because the write set is a fixed, declared list.

### 3.4 Git hygiene

Treat the synced zones like `vendor/` — they're reproducible from `composer.lock`:

```gitignore
# Codey — vendored, synced on install. Reproducible via committed composer.lock.
# NEVER hand-edit these; overrides live in your project-owned brand layer.
src/assets/css/codey/
src/site/plugins/codey/
src/assets/fonts/codey/
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
2. **Project global (tier 2)** — `@import "./brand.css"` *last* in `main.css`. Its
   `@theme` block overrides tokens; Tailwind v4 merges `@theme` blocks and the
   **last declaration of a token wins**. This is where a per-project Utopia rescale
   or a colour rebrand lives.
3. **Per-template (tier 3)** — `src/assets/css/templates/{template}.css`,
   auto-loaded only on that template via Kirby's `css('@auto')`. Uses `var(--token)`
   at runtime; if it needs `@apply`, it must begin with `@reference "tailwindcss";`
   because it compiles standalone.

On the **Kirby side** the same idea applies by *name*: a project snippet, template,
or blueprint with the same name as a core one wins, because Kirby resolves
site-level definitions over plugin-registered ones. So vendored PHP is never edited
either — you shadow it.

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
@import "./brand.css";
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
@import "./globals.css";                       /* :root globals + @font-face (unlayered) */
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
stacks (`--head-font`, `--med-font`, `--ital-font`, `--cond-font` over a
`--font-fallback`), and `--blur`, `--glass-transparency`, `--radius-lg`,
`--border-radius`.

**Rescaling per project:** don't touch `theme.css`. Redeclare the tokens you want
to change in your `brand.css` `@theme` — because it loads last, it wins:

```css
/* brand.css */
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

It also declares the **secondary** `@font-face` weights (`Gotham-Med`,
`Gotham-Ital`, `Gotham-Med-Cond`) pointing at `../fonts/codey/`. Critical fonts
(the headline and book weights) are meant to be inlined per-page in your `<head>`
for first paint; these secondary weights load with `main.css`.

> **Font caveat.** The package ships the `fonts/codey/` *zone* but **not** the
> licensed Gotham/Gradual files themselves (a licensing matter). Out of the box the
> `@font-face` `src` URLs will 404 and text falls back to `--font-fallback`
> (system UI). For a real site you must either (a) drop your own licensed font
> files into the package's `fonts/` so they sync in, or (b) point the font tokens
> at fonts you supply in your project's own `src/assets/fonts/`. Plan for this — it
> is the one part of the system that isn't turnkey.

---

## 9. Colour system — palettes + semantic themes

Colour is two layers: raw **palettes** (hex values) and **semantic themes**
(meaning-based aliases over a palette). You select a theme with a body class.

### 9.1 Palettes

The core ships a single palette; a project adds its own brand palette alongside it
(see §9.4).

- **`_codey.css`** (`.theme-codey`) — deep ocean blues. A 0–8 scale
  (`--color-0` lightest … `--color-8` darkest), plus half-steps (`--color-65`,
  `--color-75`), `--keycolor-*`, and first-paint `--color-background` /
  `--color-text` literals.

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

1. Create a project palette, e.g. `src/assets/css/brand-palette.css`, with a class
   for your brand (`.theme-acme`) declaring the `--color-0…8` scale (plus
   `--color-65` / `--color-75` half-steps and `--keycolor-*` if you use them) and
   the first-paint `--color-background` / `--color-text` literals — mirror the
   structure of `codey/palettes/_codey.css`.
2. Add a semantic mapping (mirror `codey/themes/theme-codey.css`) so `--link`,
   `--hover`, `--nav-*`, `--cta-*`, etc. resolve for your brand.
3. Import both from `main.css` **after** `codey/index.css` (or from `brand.css`),
   so they load last and win.
4. Set `class="theme-acme"` on `<body>` (via the Kirby `theme` field or hardcoded).

If you only need to *tweak* the shipped codey colours rather than define a new
brand, skip the new palette and just override the specific `--color-*` /
`--color-active-*` / semantic tokens in `brand.css`.

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

- **`body`** — `--color-text` on `--color-background`, `--leading-base`,
  `--text-base`, `geometricPrecision`, smooth scroll.
- **Headings** default to `--med-font` (Gotham-Med). Scale: `h1`=`--text-4xl`,
  `h2`=`--text-3xl`, `h3`=`--text-2xl`, `h4`=`--text-xl`, `h5`=`--text-lg`,
  `h6`=`--text-base`; `h1.super`=`--text-6xl`.
- **Heading step modifiers** — `h2.down-step`, `h2.down-step-x2`, `h2.up-step`
  nudge a heading up or down the scale without changing the tag.
- **Inline** — `strong/b/.font-medium` use the medium font; `em` uses the italic
  font (`--ital-font`) rather than a synthetic slant; links use `--link` →
  `--hover`.
- **Helpers** — `.heads` and `.decor` opt into the display font (`--head-font`),
  `.mysans` forces the medium font, `.leading-tighter` tightens line-height,
  `.small` steps down to `--text-sm`.

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
system dictating your component markup. Override any literal in `brand.css`.

---

## 15. The Kirby plugin (`codey/`)

Registered as `Kirby::plugin('ianhobbsmedia/codey', …)`, it provides snippets, a
blueprint field, and an example template. **Everything is overridable by name** — a
site-level snippet/template/blueprint wins over the plugin's, so you never edit
vendored PHP.

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

### 15.5 The layout blueprint field — `fields/codey-layout`

Extend it in a page blueprint:

```yaml
layout:
  extends: fields/codey-layout
```

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
<?= css('assets/css/main.css') ?>   <!-- core (tier 1) + brand.css (tier 2) -->
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
  CSS/JS. The plugin at `src/site/plugins/codey/` mirrors to
  `build/site/plugins/codey/`.
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
tiers (`main.css`, `brand.css`, template files, shadowed snippets) are untouched
and continue to win. If you committed the vendored folder, review the diff; if you
gitignored it, the new version is pinned in the updated `composer.lock` — commit
that.

---

## 18. Fresh-project checklist

1. `composer.json` with the Codey require + `codey-sync` scripts (`.cjs` path,
   including `/package/`).
2. `package.json` with Tailwind v4 + Alpine and a `build:css` script.
3. `composer install` → `npm install` → confirm the three zones synced.
4. Gitignore the three zones + `.codey-version`; commit `composer.lock`.
5. Author `src/assets/css/main.css` — `@import "tailwindcss"`, `@layer` order,
   `@source` globs, `@import "./codey/index.css"`, then `@import "./brand.css"`.
6. Author `src/assets/css/brand.css` — `@theme` token overrides (type rescale,
   colours).
7. Supply real font files (see §8 caveat) into the package `fonts/` or point the
   font tokens at your own.
8. Pick/build a colour theme; set the `.theme-*` class on `<body>` (via a Kirby
   `theme` field or hardcoded).
9. Author your `head` snippet with `css('assets/css/main.css')` + `css('@auto')`.
10. Author `default.php` using `codey/layout` + `codey/layouts`; add
    `layout: { extends: fields/codey-layout }` to your page blueprint.
11. `npm run build:css`, point Kirby at `build/`, run.

---

## Appendix A — Token quick reference

**Type scale:** `--text-xs · sm · base · lg · xl · 2xl · 3xl · 4xl · 5xl · 6xl · 7xl · 8xl` (fluid `clamp()`).

**Spacing scale:** `--spacing-4xs · 1 … 17` plus pair steps `--spacing-3-s · 4-m · 5-l · 6-xl · 7-2xl · 8-3xl · 9-4xl … 14-9xl`.

**Line-heights:** `--leading-tight · mid · mad · big · tighter · base · head`.

**Fonts:** `--head-font` (Gradual) · `--med-font` (Gotham-Med) · `--ital-font` (Gotham-Ital) · `--cond-font` (Gotham-Med-Cond) · `--font-fallback`.

**Effect tokens:** `--blur` · `--glass-transparency` · `--radius-lg` · `--border-radius` · `--padding`.

**Palette scale (per theme):** `--color-0 … 8/9` (+ `--color-65`, `--color-75`), `--keycolor-5…8`, `--color-active-1…4`.

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
