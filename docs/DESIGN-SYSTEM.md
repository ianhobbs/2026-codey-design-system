# Codey Design System — mechanism

How the system is authored and lives inside a Codey Kirby project, working *with*
the `src/ → build/` (CodeKit / Tailwind CLI) convention rather than against it.

## Delivery (why this shape)

Codey is a **Git starter you clone**, not a dependency you install. The theme runs
in place and is compiled by the project's own pipeline. This replaced an earlier
Composer-plugin + sync-script design whose two payloads (synced CSS, registered
PHP) had different lifecycles and no single install path. See
[ARCHITECTURE.md](ARCHITECTURE.md) and [Theme-Strategy.md](Theme-Strategy.md).

- **Not a referenced dependency** — the theme's CSS is Tailwind *source*; it must
  sit in `src/` so the project's build compiles it. In a clone, it simply does.
- **Not a submodule/subtree** — a clone-to-start starter needs no nested `.git`;
  you own the tree and diverge from it per project.
- **Composer for Kirby only** — `build/composer.json` pulls Kirby + PHP deps into
  `build/`. npm sits at root for Tailwind + Alpine. Neither delivers the theme; the
  theme is already in `src/`.

## Install flow

```
cd build && composer install   → Kirby + vendor into build/ (gitignored)
npm install                    → Tailwind + Alpine + esbuild
npm run build                  → src/assets → build/assets  (Tailwind CSS + Alpine bundle)
                                 src/site   → build/site    (mirror)
npm run serve                  → Kirby dev server on :8000
```

CodeKit does the same `src → build` work live while you edit.

## The core / project split

`src/` is the single source of truth. Within it, `codey/` folders are the system
core; everything else is the project. There is no sync and no clobber risk —
nothing overwrites your files, because there is no install step writing into the
tree.

| Core (`codey/`, versioned) | Project (yours) |
|---|---|
| `src/assets/css/codey/**` | `src/assets/css/main.css`, `_brand.css`, `_brand-palette.css`, `templates/*.css` |
| `src/assets/js/codey/**` | `src/assets/js/**` |
| `src/site/snippets/codey/**` | `src/site/{templates,snippets,controllers,blueprints,config}` |

## Override contract (load order = precedence)

1. **Core** — `main.css` does `@import "./codey/index.css"` (tokens, globals,
   default theme, bespoke layout/grid/type/element libraries).
2. **Project brand** — `@import "./_brand.css"` *last* in `main.css`. Its `@theme`
   overrides tokens; Tailwind v4 merges `@theme` blocks and the last declaration
   wins. Optional `_brand-palette.css` (generated) is imported from here.
3. **Per-template** — `src/assets/css/templates/{template}.css`, auto-loaded only
   on that template via `css('@auto')`. Uses `var(--token)` at runtime; if it needs
   `@apply`, it starts with `@reference "tailwindcss";`.

**Kirby side:** Codey's snippets live under `src/site/snippets/codey/` and are
called by logical name (`snippet('codey/layout')`); field blueprints are referenced
`extends: fields/layout`. To customise, edit the project's own templates/snippets
directly (you own the clone), or — if you keep the core pristine — drop a same-named
file into `site/`, which Kirby resolves before anything else.

Precedence: **project `site/` files → (future) plugin → Kirby core.**

## What the core contains

- **Tokens** — `codey/theme.css` (`@theme` Utopia fluid type/space scale, replacing
  Tailwind's default ramps) + `codey/globals.css` (`:root` globals + `@font-face`).
- **Colour system** — raw palettes (`palettes/_codey`) + semantic themes
  (`themes/theme-codey`), decoration stripped.
- **CSS core** (`codey/lib/`) — `layout.css` (two-axis page frame), `grid.css`
  (content grid devices), `typography.css`, `elements.css`.
- **Component seeds** (`codey/lib/`, opt-in) — `form`, `accordion`, `transitions`,
  `cards`: generically useful tokens with guidance comments, commented in the
  manifest so they ship zero bytes until a project uncomments them.
- **Layout engine** (`src/site/snippets/codey/`) — the `codey/layout` shell,
  `codey/header` / `codey/footer`, and the `codey/layouts` layout-field renderer,
  plus the `layout` / `cover` field blueprints.

## Opinionated manifest

`src/assets/css/codey/index.css` is the toggle sheet: core imports are always on;
optional components sit as **commented imports**. A project uncomments only what its
markup uses — no accordion markup, no accordion bytes.

## Tools

`scripts/brand-palette.cjs` generates a perceptually even brand palette in OKLCH
(`npm run palette -- --dark … --light … --mid …`). `scripts/sync-site.mjs` mirrors
`src/site → build/site` for the no-CodeKit build. Utopia regeneration and the
styleguide preview remain workshop tools.
