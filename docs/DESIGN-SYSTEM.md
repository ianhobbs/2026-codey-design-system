# Codey Design System — mechanism

How the theme is authored once and consumed by many Codey Kirby projects without
fighting the `src/ → build/` (CodeKit / Tailwind CLI) convention.

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
CSS core + tokens, the Kirby plugin, fonts, the sync manifest, and the sync
script. You refine the theme *here* and tag releases.

**A consuming project** (`example-project/` demonstrates it) — authors only its
own files; the `codey/` folders arrive via Composer and are gitignored +
restored on install, exactly like `vendor/`.

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
| `src/site/plugins/codey/`          | `package/kirby/` |
| `src/assets/fonts/codey/`          | `package/fonts/` |

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
both present, `--text-base` and `--color-accent` resolve to the brand values.

## Tools (in `package/`, out of scope to rebuild here)

Utopia regen + `convertVariables.js` (token scale), SVG colour-harmony +
`svg-render` (icon variants), styleguide-builder (preview). The sync script is
the one new tool this design adds.
