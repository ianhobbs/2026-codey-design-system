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
npm install                    → Tailwind + Alpine (CodeKit bundles the JS)
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

| Tier | On a Codey update | Where |
| --- | --- | --- |
| **CORE** | overwritten wholesale | `src/assets/css/codey/**`, `src/assets/js/codey/**`, `src/site/snippets/codey/**` |
| **SEED** | never touched; yours forever | `src/assets/css/brand/**` |
| **PROJECT** | Codey never had it | `main.css`, `src/assets/css/{lib,templates}/**`, `src/assets/js/**`, `src/site/{templates,controllers,blueprints,config}` |

Changing a **value** is a SEED edit; changing a **rule** is a CORE fix. Core carries
no brand values, so any diff under `codey/` is either a fix worth exporting upstream
or a mistake.

## Override contract (load order = precedence)

`main.css` sets the order, and it is deliberate — seeds first so core can read the
tokens, your overrides last so they always win:

1. **Seed** — `@import "./brand/_tokens.css"`, `_globals.css`, `_theme-codey.css`.
   Tokens, `:root` constants, `@font-face`, colour flavour. Project-owned.
2. **Core** — `@import "./codey/index.css"` (bespoke layout/grid + base type/elements).
   Rules only; reads the tokens above, defines none of them.
3. **Opt-in core** — commented `@import "./codey/lib/{transitions,form,accordion,cards}.css"`
   in `main.css`. Uncomment only what the project's markup uses.
4. **Generated Utopia** — `@import "./lib/utopia-export.css"`, overriding the seed ramp.
5. **Project overrides** — `@import "./brand/_overrides.css"` *last*. Its `@theme`
   beats every earlier `@theme` (Tailwind merges them, last declaration wins), and
   its `@layer bespoke` / `@layer base` blocks beat same-layer core rules on source
   order. This is where a project overrides a core *rule* without editing core.
6. **Per-template** — `src/assets/css/templates/{template}.css`, auto-loaded only
   on that template via `css('@auto')`. Uses `var(--token)` at runtime; if it needs
   `@apply`, it starts with `@reference "tailwindcss";`.

All partials are underscore-prefixed so CodeKit skips them — they are `@import`
fragments and compiling one standalone emits broken CSS. `main.css` is the only
entry point.

**Kirby side:** Codey's snippets live under `src/site/snippets/codey/` and are
called by logical name (`snippet('codey/frame')`); field blueprints are referenced
`extends: fields/layout`. To customise, edit the project's own templates/snippets
directly (you own the clone), or — if you keep the core pristine — drop a same-named
file into `site/`, which Kirby resolves before anything else.

Precedence: **project `site/` files → (future) plugin → Kirby core.**

## What the core contains

- **Tokens (SEED, not core)** — `brand/_tokens.css` (`@theme` Utopia fluid type/space
  scale, replacing Tailwind's default ramps) + `brand/_globals.css` (`:root` globals
  + `@font-face`). Shipped once, then project-owned.
- **Colour system (SEED)** — raw palette (`brand/_palette-codey.css`) + semantic
  flavour (`brand/_theme-codey.css`), decoration stripped.
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
