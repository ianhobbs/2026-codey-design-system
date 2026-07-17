# Codey Design System — mechanism

How the theme is authored once and consumed by many Codey Kirby projects without
fighting the `src/ → build/` (or `public/`) (CodeKit / Tailwind CLI / Vite) convention.

## Decision trail (why this shape)

- **Not an npm/Composer *dependency* you reference in place** — the theme must
  land as plain source under `src/` so a project's own pipeline (CodeKit, Vite,
  or pure npm+Tailwind) compiles it. It's a *toolset that integrates into the
  build*, not a package the build imports from `vendor/`/`node_modules/`.
- **Not git subtree/submodule** — subtree loses explicit version pinning;
  submodule drops a nested `.git` into `src/` that fights CodeKit's watcher and
  needs an init step, breaking "just works with any build."
- **Composer for versioning + a copy script for placement.** Composer fetches
  and semver-pins the package into `vendor/`; a `post-install` script copies its
  source into `src/`. Composer is the *version* channel; the script is the
  *placement* channel. npm lives beside it at root for the build toolchain
  (Tailwind, Alpine) — Composer can't fetch those.

## The two repos

**`package/`** — the canonical theme (versioned, `composer require`d). Holds the
CSS core + tokens, the Kirby plugin, the sync manifest, and the sync
script. You refine the theme *here* and tag releases.

**A consuming project** — authors only its own files; the `codey/` folders arrive
via Composer and are gitignored + restored on install, exactly like `vendor/`.

## Install flow

```
composer install  → fetches package to vendor/, post-install-cmd runs codey-sync
npm install       → Tailwind/Alpine; postinstall re-runs codey-sync (belt + braces)
npm run build:css → src/assets/css/main.css → build/assets/css/main.css (or CodeKit/Vite)
```

## Zone boundary (clobber-safety)

`codey-sync.json` declares source→dest zones. The script wipes and re-copies
**only** those exact dest paths; everything else in `src/` is project-owned and
never touched.

| Synced zone (gitignored, restored) | From package |
|------------------------------------|--------------|
| `src/assets/css/codey/`            | `package/css/` |
| `src/assets/js/codey/`             | `package/js/` |
| `src/site/plugins/codey/`          | `package/kirby/` |

Because the write set is a fixed, declared list, clobbering a project file is
structurally impossible — proven by re-syncing over a tampered vendored file:
the vendored file is restored, `brand.css` and `templates/*.css` survive.

## Override contract (load order = precedence)

1. **Core** (tier 1) — `main.css` does `@import "./codey/index.css"`.
2. **Project global** (tier 2) — `@import "./brand.css"` *last* in `main.css`.
   Its `@theme` overrides tokens; Tailwind v4 merges `@theme` blocks and the
   last declaration wins. This is where a per-project Utopia rescale lives.
3. **Per-template** (tier 3) — `src/assets/css/templates/{template}.css`,
   auto-loaded only on that template via `css('@auto')`. Uses `var(--token)` at
   runtime; if it needs `@apply`, it starts with `@reference "tailwindcss";`.

**Kirby side:** a project overrides any core snippet/template/blueprint *by
name* — Kirby resolves site-level over plugin-registered, so vendored PHP is
never edited either.

Verified token resolution: with the core default and the `brand.css` override
both present, `--text-base` and `--color-active-1` resolve to the brand values.

## What's extracted so far

The package is populated, not stubbed. Verified through the sync → import →
Tailwind v4 compile chain (see [ROADMAP.md](ROADMAP.md) for full status):

- **Tokens** — `theme.css` (`@theme` Utopia type/space scale) + `globals.css`
  (`:root` globals + secondary `@font-face`).
- **Colour system** — raw palettes (`_codey`, `_caramel`, `_users` template) +
  semantic themes (`theme-codey`, `theme-caramel`), decoration stripped.
- **CSS core** (`lib/`) — `layout.css` (two-axis frame + `.blocks-grid`),
  `typography.css`, `elements.css`.
- **Component seeds** (`lib/`, opt-in) — `form`, `accordion`, `transitions`,
  `cards`: generically useful tokens with guidance comments, commented in the
  manifest so they ship zero bytes until a project uncomments them.
- **Kirby plugin** — `codey/layout` shell + `codey/header` / `codey/footer` /
  `codey/layouts` renderer + `fields/codey-layout` blueprint, registered in
  `index.php`.

Still pending: the `.text` prose component, full optional-component CSS, the
icons, the generator tools, and a real Kirby run. (Fonts are a deliberate project
override, not bundled.)

## Opinionated manifest

`package/css/index.css` is the toggle sheet: core imports are always on; optional
components sit as **commented imports**. A project uncomments only what its markup
uses — no accordion markup, no accordion bytes.

## Tools (in `package/`, out of scope of the extraction so far)

Utopia regen + `convertVariables.js` (token scale), SVG colour-harmony +
`svg-render` (icon variants), styleguide-builder (preview). The sync script is
the one new tool this design adds.
