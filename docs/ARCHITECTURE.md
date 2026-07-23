# Architecture

## Why this shape

Codey is delivered as a **Git starter**, not a package: you clone it to begin a
project, and the clone *is* the project. This follows the
[kirby-baukasten](https://github.com/tobimori/kirby-baukasten) model — the theme
runs in place, compiled by the project's own `src/ → build/` pipeline, rather than
being referenced from `vendor/`/`node_modules/`.

An earlier version tried to be a Composer dependency that injected itself into a
consuming project (CSS synced into `src/`, PHP registered from a plugin). That
split one release into two payloads with different lifecycles and no single install
path that satisfied both. The survey behind the decision to drop it is in
[Theme-Strategy.md](Theme-Strategy.md). The Git model removes the problem outright:
there is one tree, one build, one source of truth.

## Delivery: clone, install, build

```
git clone …           → the starter becomes your project
cd build && composer install   → Kirby + PHP deps (Composer lives in build/)
npm install && npm run build    → src/assets → build/assets, src/site → build/site
```

`src/` is authored; `build/` holds the compiled result. The server runs this repo
directly, so the compiled `build/assets` + `build/site` mirror are committed, while
Composer-installed dirs, rsynced binaries, and everything Kirby generates
(media/cache/sessions/content) are gitignored. CodeKit watches
`src/` live; `npm run build` is the no-CodeKit equivalent. Nothing is "installed
into" the project — Codey's files are the project's files.

## Layers (what the starter ships)

```
┌──────────────────────────────────────────────┐
│ project layer  (main.css, _brand.css,         │  ← YOURS — edit freely
│                 templates, snippets)          │
├──────────────────────────────────────────────┤
│ Kirby layout engine  (layout shell,           │  ← src/site/snippets/codey/*
│   header/footer, layout-field renderer)       │
├──────────────────────────────────────────────┤
│ CSS core  (layout frame, grid, type, elements)│  ← src/assets/css/codey/lib
├──────────────────────────────────────────────┤
│ Colour system  (palettes + semantic themes)   │  ← src/assets/css/codey/{palettes,themes}
├──────────────────────────────────────────────┤
│ Tokens  (@theme Utopia type/space, globals)   │  ← src/assets/css/codey/{theme,globals}.css
└──────────────────────────────────────────────┘
```

Dependencies point downward only. A rule references the tokens below it; it never
reaches up into the project layer.

## The core / project boundary

The clone is all yours to edit, but Codey marks what belongs to the *system* so
updates stay clean: everything under a **`codey/` folder is core**.

| Core (`codey/`) — update by pulling, don't edit | Project — edit freely |
|---|---|
| `src/assets/css/codey/**` | `src/assets/css/main.css`, `_brand.css`, `_brand-palette.css` |
| `src/assets/js/codey/**` | `src/assets/js/**` |
| `src/site/snippets/codey/**` (layout engine) | `src/site/{templates,snippets,controllers,blueprints,config}` |

Because Codey is *cloned*, this boundary is a convention (a clean-merge aid), not a
lock. CSS overrides go in `_brand.css` (later imports win). PHP customisation is
just editing the *project* files — Kirby also resolves `site/` files before any
plugin, so the boundary stays valid if the engine is later promoted to a plugin.

### The plugin-promotion path (optional, future)

If you later run several live sites that should pull Codey updates *in place*
(rather than each being a fork), the layout engine under `src/site/snippets/codey/`
plus the `layout`/`cover` field blueprints can be lifted into an auto-loaded
`src/site/plugins/codey/` module (Firma-style: an `index.php` that walks the folder
and registers each file, so there's no hand-maintained list). Kirby's `site/`
override precedence then gives you the clean "update the engine, keep your
overrides" story. This is deliberately **not** done by default — for a clone-to-
start starter it adds indirection and reintroduces block-name registration for no
gain. It's a mechanical change to make only when in-place updating becomes the
real workflow.

## The invariant that prevents re-complexification

Each item that enters the `codey/` core must pass two tests:

1. **No hardcoded value a token could express.** Colours, spacing and type sizes
   come from the `@theme` tokens / palettes. Override the tokens, keep the rule —
   that is what makes a rule portable across brands.
2. **No project-specific content.** No client logos, licences/keys, decorative
   SVGs, or assumptions about one site's page model. If it only makes sense for one
   project, it stays in the project layer.

Anything failing either test is *project*, not *core*. On extraction, coupled
pieces were decoupled (fonts repointed, decorative SVGs and product rules stripped
to placeholders, project blocks dropped from the layout field).

## Opinionated manifest

`src/assets/css/codey/index.css` lists the core (always on) and the **optional
components commented out**. A project uncomments only the ones whose markup it uses
— no accordion markup means the accordion line stays commented and ships zero
bytes. The optional lib files are token *seeds* (generically useful custom
properties) with guidance comments, not full components.
