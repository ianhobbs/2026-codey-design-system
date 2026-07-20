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

## ⬜ Phase 5 — Tools (`package/` — out of scope of the extraction so far)

- ⬜ Utopia regen + `convertVariables.js`, SVG colour-harmony/`svg-render`,
  styleguide-builder. The sync script is the one new tool this design adds.

## ⬜ Phase 6 — Run in a real Kirby install

- ⬜ `composer require` the package into a Kirby project, sync, render. The
  templates are structurally decoupled but not yet execution-tested (no PHP
  runtime in the build sandbox).

## ⬜ Phase 7 — Dogfood

- ⬜ Migrate `codey-new-2025` to consume the package instead of its local copies.

---

## Guardrails

- Nothing hardcoded that a token could express.
- Nothing project-specific in the package (content, licenses, client logos,
  decorative backgrounds, keys).
- Versioned releases via Composer; projects pin a version.
- `build/` in the source is never an extraction source — only `src/`.
