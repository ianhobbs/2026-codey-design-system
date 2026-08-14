# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Codey** — a Kirby CMS design-system starter, and the **upstream source of truth**
for every site built on it. Projects clone this repo, run `rm -rf .git && git init`
(see README quickstart), and become their own repo. They never pull from here again.

That one-way, history-severing delivery model is the fact that shapes everything
else in this file.

This repo is also a runnable Kirby site in its own right — full `src/ → build/`
toolchain, committed `build/`, the works. It is a starter, not a package.

## Commands

```bash
npm run setup           # composer install (Kirby into build/) → seed → build
npm run serve           # cd build && composer start → http://localhost:8000
npm run build           # site + assets (convert → tails)
npm run site            # mirror src/site → build/site (scripts/sync-site.mjs)
npm run tails           # one-shot Tailwind build (exits). tails:watch to watch.
npm run convert         # regenerate lib/utopia-export.css from lib/utopia-pre.css
npm run seed            # populate build/content from sample-content/
npm run palette -- --dark "#111318" --light "#f6f8fb" --mid "#c8452f"
                        # → src/assets/css/brand/_palette.css
```

No test suite, no lint script.

**Build-script convention: `.cjs` for CommonJS, and the extension must match the
`package.json` entry.** `"type": "module"` makes a bare `.js` file ESM, so a
CommonJS script named `.js` throws on its first `require`. `convertUtopia` was
exactly that — declared as `convertUtopia.cjs`, on disk as `convertUtopia.js`, so
`npm run convert` (and therefore `assets` and `build`) failed. Now `.cjs`, matching
the sibling `scripts/brand-palette.cjs`. Keep new CommonJS build scripts on `.cjs`;
`scripts/*.mjs` are the ESM ones.

## The three tiers

Every file is in one tier, sorted by **what happens to it on a Codey update** —
not by who wrote it. Getting this wrong is what broke the first version.

```text
CORE — overwritten wholesale on update; a project must never hand-edit it
  src/assets/css/codey/**       layout/grid/type/element RULES, ZERO brand values
  src/assets/js/codey/**
  src/site/snippets/codey/**    the layout engine

SEED — shipped once at clone time, then owned by the project forever.
       An update never touches it; it is never accepted back upstream.
  src/assets/css/brand/**       tokens, globals, colour flavour, palette, overrides

PROJECT — Codey never had it
  src/assets/css/main.css       entry point + the opt-in component list
  src/assets/css/lib/**         generated Utopia scale
  src/site/{templates,controllers,blueprints,config}
```

**The test:** a **value** (font, colour, measure) is SEED. A **rule** (how the grid
resolves, how a block bleeds) is CORE.

Because CORE holds no brand values, the boundary is machine-checkable: **any diff
under `codey/` is either a core fix or a mistake.** Keep it that way — the moment a
brand value lands in `codey/`, downstream projects have to edit core to override it,
and the whole model collapses. That is precisely what happened before `brand/`
existed (`codey/theme.css` and `codey/globals.css` held project fonts).

See [src/assets/css/brand/README.md](src/assets/css/brand/README.md).

## Receiving fixes from a project

Projects share **no git history** with this repo, so merge, rebase, cherry-pick and
subtree all have nothing to anchor to. Fixes arrive as **patches** — git blobs are
content-addressed, so an unmodified core file has the same blob SHA in both repos
and a diff against it applies cleanly across disjoint histories.

The producing side owns the tooling. In the Rosie Boylan project:

```bash
npm run codey:export             # dry run
npm run codey:export -- go       # apply here, unstaged, for review
npm run codey:export -- --drift  # report divergence across the three core zones
```

It never commits or pushes. Review the unstaged diff here, rebuild, commit yourself.

Note `docs/IMPLEMENTATION-GUIDE.md` §17 documents a `git pull` / cherry-pick update
flow — that does not work for clones that severed history, which is all of them.

## Naming: the underscore convention

CodeKit skips files whose names begin with `_`, so the prefix tags a partial as
non-renderable. Everything in `brand/` is underscored because each is an `@import`
fragment carrying an `@theme` block with no `@import "tailwindcss"` of its own —
compiling one standalone emits broken CSS into `build/`.

`main.css` is the only real entry point. Files under `codey/` are **not** yet
underscored; that is a known open item in [docs/ROADMAP.md](docs/ROADMAP.md), left
alone because renaming them breaks the documented `@import "./codey/index.css"`
entry point for every existing consumer.

## Gotchas

- **CodeKit** holds this project open and rewrites `build/` under you. Don't fight
  it with `.codekitignore` or hand edits to `config.codekit3` — it rewrites that
  file too. Ask Ian to pause it when a task needs `build/` to hold still.
- Compiled `build/assets/{css,js}` and `build/site/**` are **committed** — the
  server runs this repo in place.
- `build/kirby`, `build/vendor`, `build/site/plugins` are Composer-installed and
  gitignored. Run `npm run setup` on a fresh clone or Kirby throws
  `NotFoundException: The home page does not exist`.
