# Extraction Roadmap

How the design system gets populated from the source project without dragging
its complexity across. Source: `/Volumes/2000/Sites/codey-new-2025` (the
hand-authored layer is `src/`; `build/` is the compiled runnable Kirby install
and is **not** an extraction source).

The sequence matters: **tokens → CSS → Kirby → starterkit → migrate.** Each
phase leaves both repos in a working state, so you are never mid-air.

---

## Phase 0 — Audit & tag

Walk `src/` and label every file as one of:

- **primitive** — reusable, portable → promote to a package
- **coupled** — reusable but wired to project specifics → decouple, then promote
- **project** — stays in the source site → do not move

The inventory below is the first pass at this tagging.

## Phase 1 — Tokens first (`packages/tokens`)

Everything depends on tokens, so they move first.

| Source | Target | Notes |
|--------|--------|-------|
| `src/assets/css/themes/_palette-codey.css` | `packages/tokens/src/` | Base palette |
| `src/assets/css/themes/_palette-caramel.css` | `packages/tokens/src/` | Alt palette (brand variant) |
| `src/assets/css/themes/_palette-users.css` | `packages/tokens/src/` | User/account palette |
| `src/assets/css/themes/theme-*.css` | `packages/tokens/src/` | Theme compositions (space/text/dark/light) |
| `src/assets/css/utopia/utopia-pre.scss`, `src/assets/css/lib/utopia-*.css` | `packages/tokens/src/` | Fluid type + space scale |

**Decouple step:** as you move, replace any hardcoded value still living in the
source CSS with a token reference *in place*. This also cleans the source
project — it is not throwaway work.

## Phase 2 — CSS primitives (`packages/css`)

Move the framework, cutting each dependency on project-specific markup as you go.

| Source | Target | Notes |
|--------|--------|-------|
| `src/assets/css/layout.css` | `packages/css/src/lib/` | Grid + container system (12-col, subgrid) |
| `src/assets/css/lib/elements.css` / `elements.styl` | `packages/css/src/lib/` | Base elements |
| `src/assets/css/lib/typography.css` / `typography.styl` | `packages/css/src/lib/` | Type primitives |
| `src/assets/css/lib/effects.css` / `effects.styl` | `packages/css/src/lib/` | Effects (incl. off-screen transforms) |
| `src/assets/css/lib/form.css`, `_form.css` | `packages/css/src/lib/` | Form primitives |
| `src/assets/css/lib/_accordion.css`, `lightbox.css` | `packages/css/src/lib/` | Standalone components |
| `src/assets/css/component/*.styl` (content, indicators, media) | `packages/css/src/lib/component/` | Component CSS |

**Watch for:** the off-screen / off-page / menu-stack / push-stack utilities
(documented in the source `codey-arch.md`). These are the crown jewels — verify
they reference tokens only and carry no site-specific selectors before promoting.

## Phase 3 — Kirby plugin (`packages/kirby`)

Promote reusable Kirby pieces; register each in `index.php`.

| Source | Target | Notes |
|--------|--------|-------|
| `src/site/snippets/accordion.php`, `image.php`, `cover-image.php`, `cta-shape.php`, etc. | `packages/kirby/snippets/` | Generalise hardcoded paths/content |
| `src/site/snippets/blocks/*` | `packages/kirby/snippets/blocks/` | Reusable block renderers |
| `src/site/blueprints/blocks/*`, `fields/*`, `sections/*` | `packages/kirby/blueprints/` | Field/section/block definitions |
| `build/site/templates/*` (reusable ones, e.g. note/notes) | `packages/kirby/templates/` | Only generic templates |
| `src/site/models/note.php`, `service.php` | `packages/kirby/models/` | Only generic models |

**Exclude:** account/auth snippets tied to this site, config with licenses/keys,
collections that assume this site's content model. Those are **project**.

## Phase 4 — Assets (`packages/assets`)

| Source | Target |
|--------|--------|
| `src/assets/fonts/*` | `packages/assets/fonts/` (licensed fonts only — check EULAs before redistributing) |
| `src/assets/icons/*` (system icons, not client logos) | `packages/assets/icons/` |

Client logos (RosieBoylan, MHNSW, TCS, etc.) are **project** — leave them out.

## Phase 5 — Build tooling (`build/`)

Reproduce the Stylus → CSS pipeline (currently driven by CodeKit
`config.codekit3`) as a scriptable build so `npm run build` works without CodeKit.
Wire `packages/tokens` and `packages/css` build scripts to it.

## Phase 6 — Prove it (`starterkit/`)

Stand up the minimal Kirby starterkit consuming both packages and render it. If
it runs standalone, the extraction is sound.

## Phase 7 — Dogfood

Migrate `codey-new-2025` to consume the published packages instead of its local
copies. If the original site still works pulling from the kit, you are done —
and every future site starts here.

---

## Guardrails

- Nothing hardcoded that a token could express.
- Nothing site-specific in a package (content, licenses, client logos, keys).
- Versioned releases; sites pin a major version.
- `build/` in the source is never an extraction source — only `src/`.
