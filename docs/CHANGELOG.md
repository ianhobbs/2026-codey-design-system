# Changelog

Notable changes to the Codey design system. Newest first.

Codey clones sever git history (`rm -rf .git && git init`), so nothing here
reaches an existing project automatically. Treat each entry as a manual
migration list.

**Versions follow the git tags.** `package.json` had drifted a major ahead —
it read `4.0.0` while the newest tag was `v3.0.0`, and no `v4.0.0` was ever
cut. That untagged `4.0.0` is treated as drift rather than a release, so this
entry claims the number and `package.json` was set back to match.

---

## 4.1.0 — 2026-08-15

Codey ships its first **block**, and grows the two export zones a block needs.
Origin: a responsive-image defect found on the Rosie Boylan site, where one
hardcoded `sizes` was simultaneously too small on bled rows and twice too large
on framed ones.

### Why a block changes the shape of the system

Up to 4.0.0, core was CSS, JS and layout snippets — artefacts a project imports
and never has to wire. A block is not one artefact but **three**: a renderer, a
Panel schema, and rules. Ship only the renderer and you ship a block that cannot
be configured; ship the rules as an opt-in import and you ship one that renders
unstyled.

Kirby resolves blocks at fixed paths — `snippets/blocks/{type}.php` and
`blueprints/blocks/{type}.yml` — and **those paths cannot be core zones**. A
project must stay free to add its own blocks there, and a `kirby` CLI scaffold
run after cloning writes into exactly those directories. So core files sit under
a `codey/` subdirectory and each project keeps a shim at the path Kirby expects.

That shim is not boilerplate, it is the seam: replace its body and the block
stops tracking Codey.

### Added — export zones

```js
'src/site/blueprints/codey/'    // Panel schemas for core blocks
'src/site/config/codey/'        // config fragments and templates core owns
```

`scripts/codey-export.mjs` now guards five zones instead of three. **Two limits
of the export worth knowing**, both hit while shipping this release:

- A new core file must be `git add`ed before export sees it. The patch is built
  from `git diff`, so an untracked file is silently omitted — the export reports
  success having carried nothing.
- Renames are a delete plus an add, and deletions are refused by default. Use
  `--allow-delete` deliberately.

### Added — `hero` block

A full-screen image, edge to edge, that bleeds from wherever it is dropped
(it carries `bleed-viewport`, so it does **not** need a `grid-bleed` row).

```
src/site/snippets/codey/blocks/hero.php
src/site/blueprints/codey/blocks/hero.yml
src/assets/css/codey/lib/hero.css
```

Panel controls: image, alt, caption, height (full screen / 70% / natural), focal
point (centre / top / bottom).

**It is a separate block from `image` on purpose.** `image` declares
`sizes="auto"`, which the spec only permits with `loading="lazy"` — and
lazy-loading the LCP image is the worst thing you can do to Largest Contentful
Paint. A hero needs the opposite on every axis: `eager`, `fetchpriority="high"`,
and a flat `100vw` the preload scanner can act on before layout exists. Those
two configurations cannot coexist on one element.

`hero.css` is imported from `codey/index.css`, **not** added to `main.css`'s
opt-in list. The opt-in components are token seeds you build markup on — nothing
breaks while they are commented out. `hero.css` styles markup core itself emits,
so a commented-out import would ship the block as an unstyled full-height image.
A block and its rules travel together.

### Added — `image` block renderer

`src/site/snippets/codey/blocks/image.php` — core's first block renderer.
Previously Codey shipped block *blueprints* and left rendering to Kirby's
defaults, so **existing sites will see markup they did not have before**:

- `sizes="auto, (min-width: 900px) 80vw, 90vw"` — the browser measures the real
  laid-out width instead of guessing from the viewport. Container-query units
  are invalid in `sizes` (it resolves before layout), so `auto` is the only
  container-responsive mechanism available. Browsers without support drop the
  invalid first entry and fall through to the viewport list.
- `width` / `height` emitted from the Kirby file — reserves the box (CLS).
- `srcset` on the fallback `<img>` — without one, any browser taking that branch
  downloaded the untouched master.
- `loading="lazy"` `decoding="async"`.
- One `<picture>`; the optional `<a>` opens around it rather than duplicating it.

The fallback `sizes` list is the only value in the file, so it reads
`option('codey.blocks.image.sizes', …)` rather than a literal.

### Added — `config/codey/thumbs.php`

The canonical srcset ladder, **as a template to copy, not to require**. Read by
nothing at runtime.

A `require` from `config.php` was the first shape and was withdrawn: config must
boot from itself alone. Requiring reaches out of the project into a tree that a
Codey update overwrites and a CLI scaffold can delete — and when a stylesheet
goes missing a site looks wrong, while when config.php's require target goes
missing the site does not boot.

The rule it exists to record: **the array key IS the srcset descriptor** Kirby
writes into the markup, and the browser trusts it absolutely — it never measures
the file. So the key must equal the width beside it.

### Added — `heading` block renderer

`src/site/snippets/codey/blocks/heading.php`.

`blocks/heading.yml` has long offered seven fields. Kirby's default heading
snippet renders **two** of them — `level` and `text` — and silently discarded
the rest: `position`, `class`, `bgcolor`, `margin` and the animation trio. An
editor picked an option, saved, and the page came back identical. Same silent
no-op as the `mysans` option 4.0.0 fixed, across five fields instead of one.

The snippet wires them up. No new design — the blueprint's existing promises,
kept. `level` is allowlisted against `h1`–`h6` before it reaches the markup,
since it lands in tag-name position, and a heading with nothing set still comes
out as a bare `<h2>`.

### Fixed — blueprint-authored utilities had no CSS

Two of those fields are selects of **literal Tailwind class names**
(`position`: `text-left|text-center|text-right`, `margin`: `my-3 md:my-5` and
friends). Blueprints are `.yml`, and `main.css` scans PHP, content and JS — not
`.yml`. So a utility reached the bundle only once some page's content already
contained it.

That failure is delayed and partial, which is what makes it hard to see: `my-3`
and `md:my-5` were present, while `my-5`, `md:my-7`, `lg:my-11`, `text-center`
and `text-right` were not. Options nobody had used yet looked permanently
broken while their neighbours worked, and wiring the snippet alone would only
have moved the silent failure one layer down.

`main.css` now safelists them:

```css
@source inline("text-left text-center text-right");
@source inline("my-3 md:my-5 my-5 md:my-7 lg:my-11");
@source inline("p-3 md:p-4 lg:p-5 md:p-5 lg:p-6");
```

Safelisted rather than scanned: adding `*.yml` to `@source` would also mint
utilities out of ordinary blueprint vocabulary — `block`, `inline` and `hidden`
all appear as field values — which is the noise `source(none)` was introduced to
remove. **Keep these lists in step with the blueprint options they mirror**; a
new `margin` option needs a new safelist entry or it ships dead.

`main.css` is PROJECT tier, so this one is a manual step (see Migration).

### Breaking — snippets

| Old | New |
|---|---|
| `codey/frame-hero.php` | `codey/frame-overlay.php` |

Still reserved and still unwired, so nothing to migrate unless you referenced it
(nothing did). Renamed because `hero` now names the block that ships one, and
two unrelated things called hero is the ambiguity 4.0.0 spent a release
removing. `frame-overlay` names the overlapping-grid device for what it is.

### Fixed — srcset descriptors lied

The shipped ladders declared widths the files did not have:

| Was | Actual | Ladders |
|---|---|---|
| `'2000w' => ['width' => 2200]` | 2200 | default, webp, avif |
| `'1700w' => ['width' => 1800]` | 1800 | webp |

Every browser was told those files were ~10% narrower than they are, and
selected accordingly. New ladder, identical across all three formats, every key
matching its width:

```
400 · 600 · 900 · 1200 · 1600 · 2000 · 2560
```

Note the ceiling only holds if masters reach it — **Kirby does not upscale**, so
a master narrower than a step yields a smaller file while the descriptor still
claims the full width. Same lie, per-image. Drop the top steps rather than
overstate them.

### Fixed — stale core comment

`codey/lib/grid.css` documented the `sizes` defect as open and prescribed
threading the row `theme` down into blocks to fix it. That route was rejected —
it couples every block to the grid device it sits in, which is the coupling the
two-axis model exists to prevent — and `sizes="auto"` gets the same answer by
measurement. The comment now says so.

### Migration

Core files arrive by export. **The wiring does not** — these paths are outside
every zone by design, so each is a manual step:

1. **Create `src/site/snippets/blocks/image.php`** (the directory does not exist
   yet upstream):
   ```php
   <?php snippet('codey/blocks/image', ['block' => $block]); ?>
   ```
2. **Create `src/site/snippets/blocks/hero.php`**:
   ```php
   <?php snippet('codey/blocks/hero', ['block' => $block]); ?>
   ```
3. **Create `src/site/blueprints/blocks/hero.yml`**:
   ```yaml
   extends: codey/blocks/hero
   ```
4. **Add `hero`** to `fieldsets:` in `src/site/blueprints/fields/layout.yml`.
5. **Create `src/site/snippets/blocks/heading.php`**:
   ```php
   <?php snippet('codey/blocks/heading', ['block' => $block]); ?>
   ```
6. **Add the three `@source inline(…)` lines** to `src/assets/css/main.css`
   (see "blueprint-authored utilities" above). Without them the `heading`
   block's `position` and `margin` options emit classes with no rules.
7. **Replace the `thumbs.srcsets` array** in `src/site/config/config.php` with
   the ladder from `config/codey/thumbs.php` — hardwired, not required.
8. **Rebuild CSS.** `hero.css` is imported from `index.css`, so the bundle is
   stale until Tailwind reruns.

Steps 1–4 are what an existing site needs to *use* the hero. Steps 5–6 are a
pair — either both or neither, since the snippet without the safelist just
relocates the silent failure. Step 7 is the srcset bug fix and applies whether
or not you adopt any of the blocks.

---

## 4.0.0 — 2026-08-14

A naming pass across the whole system. Almost every change is a rename, so the
old→new tables below are the entire migration. Three things were **deleted** and
one **Panel option was broken and is now fixed** — those are called out.

Verified behaviour-preserving: every class used by markup or stored content
resolved identically before and after (Codey 53 used / 30 unresolved; the
consuming Rosie Boylan site 74 / 36 — the unresolved sets are pre-existing and
unchanged).

### Why

Five words were each doing several unrelated jobs. `layout` named the page
frame, the page shell, the field renderer, the Kirby field and the mode
attribute — `<main class="layout" data-layout="spread">` used one word for two
unrelated things on one element. `theme` named the colour flavour, the page
theme field and the grid device. `gutter` named both a page inset and a grid
gap, with a comment in `layout.css` warning you which was which.

A comment that has to explain a name is a rename waiting to happen.

### Breaking — CSS classes

| Old | New | Notes |
|---|---|---|
| `.layout` | `.frame` | The page frame. `--frame-gutter` already existed and now has a frame to belong to. |
| `.plain-blocks` | `.grid-plain` | Grid devices share a `grid-*` prefix, so the prefix carries the category. |
| `.plain-blocks-padded` | `.grid-plain-padded` | |
| `.card-blocks` | `.grid-cards` | |
| `.full-bleed-grid` | `.grid-bleed` | |
| `.blocks-grid` | `.grid-12` | The generic 12-track utility, not a layout-field device. |
| `.full-bleed` | `.bleed-viewport` | The `dvw` escape hatch. `.bleed` (track opt-out) is unchanged. |
| `.full-bleed-clip` | `.bleed-viewport-clip` | |
| `.headsans` | `.subhead` | Named for the token it applies (`--font-subhead`), which need not be a sans. |
| `.heads` | *removed* | Byte-identical twin of `.decor`. Core `footer.php` now uses `.decor`. |

### Breaking — attributes

| Old | New |
|---|---|
| `data-layout="frame\|spread\|bleed"` | `data-reach="inset\|spread\|bleed"` |

The axis controls how far content reaches, and the attribute now says so. The
mode formerly called `frame` is `inset`, so the element and its state never
collide on one word: `<main class="frame" data-reach="spread">`.

### Breaking — snippets

| Old | New |
|---|---|
| `snippet('codey/layout', …)` | `snippet('codey/frame', …)` |
| `codey/layouts-hero.php` | `codey/frame-hero.php` (reserved, not yet wired up) |
| `codey/layouts-full.php` | *removed* — referenced nowhere; differed from `codey/layouts` by a wrapper element |

`codey/frame` takes `reach` where it used to take `mode`. There is no
compatibility shim: the old file is gone, so a stale `snippet('codey/layout')`
call fails loudly rather than silently rendering nothing.

`codey/layouts.php` keeps its name — it renders a Kirby **layout field**, so the
Kirby word is correct there.

### Breaking — custom properties

**Core** (`assets/css/codey/**`)

| Old | New | Notes |
|---|---|---|
| `--gutter` | `--grid-gap` | It was a grid *gap*, not a gutter. |
| `--layout-gutter` | `--frame-gutter-current` | Scoped alias of `--frame-gutter`. |
| `--layout-measure` | `--measure-page` | |
| `--columns` | `--span` | Sits on `.column`; it is a span, not a count of columns. |
| `--main-pt` / `--main-pb` | `--pad-block-start` / `--pad-block-end` | |
| `--w` / `--h` | `--ratio-w` / `--ratio-h` | Aspect-ratio media boxes. |

**Seed** (`assets/css/brand/**` — a project owns these; update in place)

| Old | New |
|---|---|
| `--body-font` | `--font-body` |
| `--bodymed-font` | `--font-body-medium` |
| `--head-font` | `--font-head` |
| `--med-font` | `--font-subhead` |
| `--ital-font` | `--font-italic` |
| `--mono-font` | `--font-mono` |
| `--body-weight` | `--weight-body` |
| `--bodymed-weight` | `--weight-body-medium` |
| `--head-weight` | `--weight-head` |
| `--med-weight` | `--weight-subhead` |
| `--mono-weight` | `--weight-mono` |
| `--leading-mid` | `--leading-subhead` |
| `--leading-mad` | `--leading-headline` |
| `--leading-head` | `--leading-headline-open` |

Font **families** moved into Tailwind's `--font-*` namespace, so they now
generate real `font-body` / `font-head` utilities, which the old names could not.
**Weights** deliberately did *not* become `--font-weight-*`: that namespace also
emits `font-{name}`, which would collide with the family's own utility. They are
plain `--weight-*` vars, consumed by rules rather than utilities.

One consequence worth knowing: naming the mono token `--font-mono` hooks it into
Tailwind's built-in `--default-mono-font-family`, so preflight now styles
`code`/`kbd`/`pre` from the same token the typographic base uses. The resolved
value is unchanged out of the box.

### Removed

| Token | Why |
|---|---|
| `--padding` | Declared in `@theme`, read by nothing. Squatted a generic name. |
| `--note-width` | Declared, read by nothing. Also collided with the `note` page type. |
| `--leading-tight` | `23.5px` — the only unit in a set of unitless ratios, and read by nothing. A line-height with a unit stops scaling with its font-size. |

### Fixed

- **`blocks/heading.yml` offered a class option `mysans` that matched no CSS
  rule.** An editor could pick it and nothing happened, silently. The option is
  now `subhead` and resolves. No content migration needed — nothing had used it.

### Changed — values

- `--leading-headline-open` is `1.08`, where the `--leading-head` it replaces was
  `1.1`. `h1` and `.up-step` render very slightly tighter.

### Changed — build

`main.css` now imports Tailwind with `source(none)` and lists every scanned path
explicitly. Automatic content detection was sweeping the whole tree and minting
utilities out of English words in CSS comments and docs — `.uppercase`, `.ring`,
`.filter`, `.resize`, `.invisible` — none referenced by any markup. Removing them
halved the bundle (Codey 25,759 → 13,074 bytes; Rosie 29,659 → 18,067).

If you add a directory that markup or content lives in, it now needs its own
`@source` line. Nothing is scanned implicitly any more.

### Content migration

The grid device names are the layout field's stored `theme` values, so existing
content must be rewritten:

```
"theme":"plain-blocks"         →  "theme":"grid-plain"
"theme":"plain-blocks-padded"  →  "theme":"grid-plain-padded"
"theme":"card-blocks"          →  "theme":"grid-cards"
"theme":"full-bleed-grid"      →  "theme":"grid-bleed"
```

`build/content` is gitignored — back it up before running this.

### Unchanged, deliberately

**Kirby's vocabulary is untouched.** The `layout` field type, `$page->layout()`,
`$layout->columns()`, `$column->span()`, `blueprints/fields/layout.yml` and
`codey/layouts.php` all keep their names.

The layout row's attr **key** is still `theme` even though it selects a grid
device — renaming it to `grid` is deferred, since it would need a second content
migration. Its *values* changed (above); the key did not.

`.track`, `.bleed`, `.column` and `.text` are unchanged. `.text` is a project
hook that core deliberately leaves unstyled.

### Documentation

The two axes are renamed in every doc and comment. In CSS Grid every row and
every column is a **track**, so "track" was never one axis's name — it is what
both are made of:

| Old | New |
|---|---|
| Skeleton axis (vertical) | **Row axis** — `<body>` row tracks: header / main / footer |
| Track axis (horizontal) | **Column axis** — `.frame` column tracks, named: full / content |
