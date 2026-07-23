# Codey

A Kirby design-system **starter**. You don't install Codey into a project — Codey
*is* the project. Clone it to begin a new site; it ships with the layout engine,
CSS token system, blocks and starter templates already wired, and a normal
`src/` → `build/` toolchain (Tailwind v4 + Alpine, compiled by CodeKit or npm).

Delivery is pure Git, modelled on [kirby-baukasten](https://github.com/tobimori/kirby-baukasten):
clone, install, build, run.

---

## Quickstart

```bash
# 1. start a project from Codey
git clone <codey-repo-url> my-site && cd my-site

# 2. detach from the template — this clone is your project's own repo,
#    it never syncs back to Codey
rm -rf .git && git init

# 3. front-end deps
npm install

# 4. one-shot setup: composer install (Kirby) + seed content + build
npm run setup

# 5. run it
npm run serve          # → http://localhost:8000  (Panel at /panel)
```

`npm run setup` is `composer install` (Kirby into `build/`) → `npm run seed`
(starter content) → `npm run build` (compile `src/assets → build/assets`, mirror
`src/site → build/site`). While working, let CodeKit watch `src/`, or run
`npm run css:watch` for live CSS. Note: CodeKit ignores `main.css` — that one is
compiled by Tailwind (`npm run css`); CodeKit handles the `src → build` mirror.

---

## How it's laid out

`src/` is the **single source of truth** — you only ever edit `src/`. The build
compiles it into `build/`, which Kirby serves.

**Git hygiene** (the server runs this repo directly, so compiled output is
committed):

- **Committed:** `src/**`, the compiled `build/assets/{css,js}` and the
  `build/site/**` mirror, and the `build/` bootstrap (`composer.json`, `index.php`,
  `.htaccess`). Rebuild after editing `src/` with `npm run build`.
- **Gitignored — Composer installs it:** `build/kirby`, `build/vendor`,
  `build/site/plugins`. The server runs `composer install`.
- **Gitignored — rsynced separately:** fonts and image/video binaries
  (`src/assets/fonts`, `*.woff2`, `*.png`, …).
- **Gitignored — runtime data:** `build/content` (seed it from the committed
  `sample-content/` via `npm run seed`), `build/media`, caches, sessions.

```
src/
  assets/
    css/
      main.css        ← YOURS — the entry Tailwind compiles
      _brand.css      ← YOURS — brand tokens, fonts, overrides (wins over core)
      codey/          ← CODEY CORE — versioned, do not edit
        index.css       tokens (@theme), globals, default theme, lib/*
        theme.css  globals.css  themes/  palettes/  lib/  templates/
    js/codey/alpine.js  ← CODEY CORE
    fonts/              ← project fonts (binaries rsynced, gitignored)
  site/
    snippets/codey/*    ← CODEY CORE — layout engine (layout, layouts, header, footer, card)
    snippets/*.php      ← YOURS — starter head, cover-image, intro, prevnext, …
    templates/*.php     ← YOURS — starter home, default, note(s)
    blueprints/         ← blocks, fields (layout/cover), pages
    controllers/        ← YOURS — starter notes/note
    config/config.php   ← YOURS
build/
  composer.json  index.php  .htaccess   ← committed bootstrap (Composer installs Kirby here)
  assets/  site/                         ← COMPILED from src/ — committed (server runs the repo)
  kirby/  vendor/  site/plugins/         ← Composer-installed — gitignored
  content/                               ← runtime data — gitignored (seed from sample-content/)
sample-content/                          ← committed starter pages (npm run seed → build/content)
package.json  config.codekit3  scripts/  docs/
```

---

## The one rule

**Edit `src/`, never `build/`** — and treat anything under a `codey/` folder as
**core**: don't edit it in place, because a Codey update replaces it. Customise in
the files marked *YOURS*.

| Codey core (update by pulling; don't edit) | Yours (safe to edit) |
|---|---|
| `src/assets/css/codey/**` | `src/assets/css/main.css`, `_brand.css`, `_brand-palette.css` |
| `src/assets/js/codey/**` | `src/assets/js/**` (your scripts) |
| `src/site/snippets/codey/**` (layout engine) | `src/site/{templates,snippets,controllers,blueprints,config}` |

To restyle, override tokens/rules in `_brand.css` — later imports win. To change a
snippet or template, edit the *YOURS* files directly (you own this clone).

> **Core boundary is by convention, not lock.** Because Codey is cloned (not
> updated in place), the `codey/` folders mark what to leave alone so a future
> merge stays clean. If you later want several live sites to pull Codey updates in
> place, the layout engine under `snippets/codey/` can be promoted into an
> auto-loaded `site/plugins/codey/` — a mechanical change, documented in
> [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

---

## Deploying

Git + Composer ship the code; rsync carries only what they don't. Set your server
once, then push/pull:

```bash
cp deploy/deploy.config.example.sh deploy/deploy.config.sh   # fill in REMOTE / PORT / paths
./deploy/deploy.sh pull content go     # server → local (content, accounts, licenses)
./deploy/deploy.sh push go             # local → server (fonts + images)
./deploy/deploy.sh glue go             # local → server (env / secrets, outside web root)
```

`deploy.config.sh` and everything in `glue/` are gitignored. Content, accounts and
licenses live on the server and are pulled down; fonts, images and env are pushed
up. Full detail in [deploy/README.md](deploy/README.md).

## Using it

- **Page shell** — `snippet('codey/layout', ['pad' => 'large'], slots: true)` wraps
  a page (two-axis frame; `pad` sets vertical rhythm, `mode` sets the track).
- **Layout field** — `snippet('codey/layouts', ['field' => $page->layout()])`
  renders a Kirby layout field into the content track.
- **Panel field** — `extends: fields/layout` (and `fields/cover`) in a page blueprint.
- **Grids** — the layout row's theme class *is* the grid: `plain-blocks` ·
  `plain-blocks-padded` · `card-blocks` (12-col family) · `full-bleed-grid`
  (auto-fit, edge to edge). Framing is a separate axis in `codey/lib/layout.css`.
- **Colours** — semantic tokens over a `0–9` scale where **0 is darkest**. Generate
  a brand palette: `npm run palette -- --dark "#111318" --light "#f6f8fb" --mid "#c8452f"`
  → writes `src/assets/css/_brand-palette.css`; import it from `_brand.css`.
- **Type & tokens** — the re-engineered Utopia fluid type/space ramps live in
  `codey/theme.css`; override any token in `_brand.css`.

---

## Client style guide

`styleguide-builder/` is a self-contained generator that reads the live tokens
(colours, type, spacing, layouts) straight from `src/` and builds a static brand
style guide clients can view.

```bash
npm run styleguide     # installs its deps, extracts tokens, writes build/styleguide/
```

Output lands at `build/styleguide/index.php` (served at `/styleguide/`). Re-run it
after changing tokens or the palette. Configure source paths in
`styleguide-builder/styleguide.config.js`.

---

## Docs

| | |
|---|---|
| [ARCHITECTURE](docs/ARCHITECTURE.md) | Why the system is shaped this way; the core/brand boundary; the plugin-promotion path |
| [DESIGN-SYSTEM](docs/DESIGN-SYSTEM.md) | The CSS core, tokens, layout engine and override model |
| [IMPLEMENTATION-GUIDE](docs/IMPLEMENTATION-GUIDE.md) | The full manual — every token, element and override |
| [codey-arch](docs/codey-arch.md) | The two-axis layout + grid architecture (also feeds the style guide) |
| [ROADMAP](docs/ROADMAP.md) | What's done and what's next |
| [Theme-Strategy](docs/Theme-Strategy.md) | The delivery-model survey behind the move to Git |
