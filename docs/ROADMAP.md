# Extraction Roadmap

How the design system gets populated from the source project without dragging its
complexity across. Source: `/Volumes/2000/Sites/codey-new-2025` (the hand-authored
layer is `src/`; `build/` is the compiled runnable Kirby install and is **not** an
extraction source).

Sequence: **tokens → colour → CSS core → Kirby → assets → tools → dogfood.** Each
step leaves the package compiling and the mechanism verifiable.

Status legend: ✅ done · 🟡 partial · ⬜ pending.

---

## ✅ Phase 1 — Tokens (`package/css/theme.css`, `globals.css`)

- ✅ `@theme` Utopia fluid type + spacing scale → `theme.css`.
- ✅ Theme-independent `:root` globals (black/white, active colours, report
  colours, note-width) + secondary `@font-face` → `globals.css`.
- Decouple: fonts removed from the package (project override — see below);
  first-paint
  hex literals kept with why-comments.

## ✅ Phase 2a — Colour system (`package/css/palettes/`, `themes/`)

- ✅ Raw palette: `_codey.css` — regenerated in OKLCH via `brand-palette.cjs`,
  0 = darkest → 9 = lightest. Project palettes are generated, not shipped.
- ✅ Semantic mapping layer: `theme-codey.css`, `theme-caramel.css` (aliases
  `--link`/`--hover`/`--blockquote-*`/`--nav-*` etc.), decorative leaf-SVG
  backgrounds + products-block overrides stripped to commented placeholders.

## ✅ Phase 2b — CSS core (`package/css/lib/`)

- ✅ `layout.css` — two-axis page frame (skeleton rows + `.layout`/`.track`
  column grid, `data-layout`/`data-pad` modes) + the generic `.blocks-grid`
  content grid (replaces opinionated `.grid-home`; `.full-bleed-grid` dropped as
  redundant to `.layout > .bleed`).
- ✅ `typography.css` — element type base on the Utopia/TW scale.
- ✅ `elements.css` — aspect-ratio media boxes.

## ✅ Phase 2c — Component token seeds (`package/css/lib/`, optional)

Distilled the *generically useful tokens* from each component (not full CSS),
shipped commented-out in the manifest as opt-in seeds with guidance comments:

- ✅ `form.css`, `accordion.css`, `transitions.css`, `cards.css`.

## ✅ Phase 3 — Kirby layout snippets + blueprint (`package/kirby/`)

- ✅ `snippets/layout.php` (slot-based two-axis shell), `header.php`, `footer.php`
  (structural, decoration stripped), `layouts.php` (layout-field renderer →
  `.blocks-grid`).
- ✅ `blueprints/fields/layout.yml` (generic column presets + block set; project
  blocks like `my-products`/`swiper` dropped).
- ✅ `templates/default.php` (example, not registered — projects own `default.php`).
- ✅ Registered in `kirby/index.php` as `codey/*`.

## ⬜ Remaining CSS components (optional, full CSS)

- ⬜ `prose.css` — the `.text` rich-text block (source `lib/typography.css`).
- ⬜ Full `form` / `accordion` / `effects` / `media` component CSS to back the
  token seeds, if/when a project wants the ready-made component rather than seeds.

## Phase 4 — Assets (icons)

- ✅ Fonts: **decided as a project override.** The package ships no fonts and no
  `@font-face` — typefaces are brand-specific. See `package/fonts/README.md`.
- ⬜ System icons (leaf set) — not client logos.

## ⬜ Phase 5 — Styleguide generator (`package/styleguide/`)

Port the `styleguide-builder` from `codey-new-2025` into the package: a build-time
tool that renders a client-facing visualisation of the design tokens and layout
rules.

**Two purposes.** For clients, a quick readable picture of the system. For us, a
deliberate **robustness test** — if a project can generate a correct styleguide
from nothing but the synced core plus its own brand files, the override seams
genuinely work. It is the first end-to-end exercise of the contract.

**The requirement it proves:** the design system *requires* a project palette and
project fonts. The styleguide must render the **user's** colours and the **user's**
faces — never the codey defaults. If it shows codey blue and Gotham on a client
site, the seam is broken.

### 5.1 Output contract

- Emit **`src/style-guide.php`** — a single PHP file at the **root of `src/`**, so
  it is **Kirby-agnostic**: not a template, not a snippet, no route, no blueprint,
  no `$page`. It mirrors to `build/style-guide.php` through the normal pipeline
  and is servable directly.
- CSS, fonts and JS are referenced from `assets/**/*` (relative) or printed inline
  into the `<head>`. No absolute paths, no Kirby helpers — that's what keeps it
  portable.
- Honours the `src → build` split and must be production-safe: authored/emitted
  into `src/`, served from `build/`.

### 5.2 Token resolution — the substantive piece

Parse the sources **in load order** and merge last-wins, mirroring the cascade:

1. `src/assets/css/codey/theme.css` — core defaults
2. `src/assets/css/_brand-palette.css` — the project's colours
3. `src/assets/css/_brand-typography.css` — the project's faces
4. `src/assets/css/_brand.css` — remaining overrides

Do **not** read only the compiled CSS: Tailwind v4 tree-shakes unused `@theme`
vars (confirmed — `--text-base` is absent until something references it), so a
compiled-only extract would silently omit tokens. Source + load order is both
complete and brand-accurate.

### 5.3 Port fixes (all confirmed broken against 2.0)

- **Palette discovery** — the extractor globs `_palette-*.css`; we ship
  `palettes/_codey.css` and projects supply `_brand-palette.css`. Currently 0
  matches. Rewrite discovery around the resolution order in 5.2.
- **Colour parsing is hex-only** — finds **0 of 14** colours in the OKLCH palette.
  Reuse the existing `parseRootColors()` matcher, which already accepts
  `oklch|lab|rgb|hsl|…`.
- **`@theme` moved** from `main.css` to `codey/theme.css` — config path.
- **Scale orientation** is now 0 = darkest → 9 = lightest; ordering and labels
  must follow, including half steps (`--color-15`, `--color-25`).
- **`codey-arch.md`** section points at the old site — drop it or promote the doc
  into the package.

### 5.4 Dependencies — allowed, but pinned and declared

Unlike the other tools, the styleguide may take dependencies (Mustache may ship
to the client as fully-supported JS). The condition is that they are **tagged and
noted** so an `npm update` in this repo surfaces them:

- `package/styleguide/package.json` with **exact pinned versions**.
- Listed in the docs, with what each is for.
- **`codey-sync.cjs` and `brand-palette.cjs` stay zero-dependency** — installing
  and using the design system must never require an npm install of the styleguide's
  deps. The styleguide is opt-in.

### 5.5 Placement

`package/styleguide/` — shipped in the dist but **not synced** into `src/` (same
treatment as `fonts/`). It runs from `vendor/…` as a build step; only its *output*
(`src/style-guide.php`) lands in the project.

### 5.6 Other tools (deferred)

- ⬜ Utopia regen + `convertVariables.js`, SVG colour-harmony / `svg-render`.

## ⬜ Phase 6 — Run in a real Kirby install

- ⬜ `composer require` the package into a Kirby project, sync, render. The
  templates are structurally decoupled but not yet execution-tested (no PHP
  runtime in the build sandbox).

## ⬜ Phase 7 — Dogfood

- ⬜ Migrate `codey-new-2025` to consume the package instead of its local copies.

---

## Known issues

### ⬜ Synced `codey/` CSS lacks the `_` partial prefix (non-critical)

Project-owned partials are underscored (`_brand-palette.css`, `_brand-typography.css`,
`_brand.css`) so CodeKit skips them — they're `@import`-ed fragments, and each
carries an `@theme` block with no `@import "tailwindcss"` of its own, so compiling
one standalone emits broken CSS.

The same is true inside the synced zone: `codey/index.css`, `theme.css`,
`globals.css` and `lib/*.css` are **not** underscored, so a CodeKit-driven project
would try to compile each one on its own and produce stray, broken output in
`build/assets/css/codey/`. Only `_codey.css` is currently protected.

**Not urgent** — projects using a Tailwind CLI script or a custom `build.mjs`
(e.g. Rosieboy) are unaffected, since those compile only `main.css`.

**Why it's deferred:** fixing it means renaming synced files and changing the
documented entry point (`@import "./codey/index.css"`), which is a breaking change
for every consumer. Worth batching into the next major rather than doing piecemeal.

Options when it's picked up: underscore everything except the entry file; or keep
names and tell CodeKit to skip the `codey/` folder; or have `codey-sync` write a
CodeKit config hint.

---

## Guardrails

- Nothing hardcoded that a token could express.
- Nothing project-specific in the package (content, licenses, client logos,
  decorative backgrounds, keys).
- Versioned releases via Composer; projects pin a version.
- `build/` in the source is never an extraction source — only `src/`.
