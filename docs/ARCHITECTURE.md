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
│ project layer  (main.css, templates,          │  ← PROJECT — edit freely
│                 snippets, generated Utopia)   │
├──────────────────────────────────────────────┤
│ brand layer  (tokens, globals, colour flavour,│  ← SEED — yours after clone
│   palette, overrides)                         │     src/assets/css/brand/*
├──────────────────────────────────────────────┤
│ Kirby layout engine  (layout shell,           │  ← CORE
│   header/footer, layout-field renderer)       │     src/site/snippets/codey/*
├──────────────────────────────────────────────┤
│ CSS core  (layout frame, grid, type, elements)│  ← CORE
└──────────────────────────────────────────────┘     src/assets/css/codey/lib
```

Dependencies point downward only — with one deliberate inversion: **CORE reads the
brand layer's tokens but never defines them.** Core is pure rules; every value it
consumes arrives as a custom property the SEED tier supplies. That is what lets
core be replaced wholesale without touching a project's design.

## The three tiers

Sorted by **what happens on a Codey update**, not by authorship:

| Tier | On update | Where |
| --- | --- | --- |
| **CORE** | overwritten wholesale | `src/assets/css/codey/**`, `src/assets/js/codey/**`, `src/site/snippets/codey/**` |
| **SEED** | never touched; yours forever | `src/assets/css/brand/**` |
| **PROJECT** | Codey never had it | `main.css`, `src/assets/css/lib/**`, `src/site/{templates,controllers,blueprints,config}` |

**The test:** changing a **value** is SEED; changing a **rule** is CORE.

Because core contains no brand values, the boundary is checkable rather than
merely conventional: **any diff under `codey/` is either a core fix worth exporting
upstream, or a mistake.**

> **This is the second attempt at the boundary.** The first put `theme.css` and
> `globals.css` — the files holding a project's fonts and tokens — *inside*
> `codey/`, while telling you never to edit `codey/`. That is unobeyable: a project
> has to set its fonts somewhere. The rule broke on first contact and the trees
> drifted. Moving those files to `brand/` is what makes the boundary real.

PHP customisation is still just editing the *project* files — Kirby resolves
`site/` files before any plugin, so the boundary holds if the engine is later
promoted to a plugin. Note the PHP side has no override chain yet, so a project
needing to change a core snippet still edits it in place; see the plugin-promotion
path below.

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
