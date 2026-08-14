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
