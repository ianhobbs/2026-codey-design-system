# `brand/` — the SEED tier

Everything in this directory is **yours**.

Codey ships these files once, when you clone the starter. From that moment the
project owns them: **no Codey update will overwrite anything here, and nothing
here is ever exported back upstream.** Edit freely.

## The three tiers

Codey sorts files by *what happens on an update*, not by who wrote them:

| Tier | On a Codey update | Where |
| --- | --- | --- |
| **CORE** | overwritten wholesale | `../codey/**` |
| **SEED** | never touched — yours forever | `./` (this directory) |
| **PROJECT** | Codey never had it | `../main.css`, templates, blueprints |

## Which tier does my change belong in?

Change a **value** — a font, a colour, a measure, a spacing step → **SEED**, here.

Change a **rule** — how the grid resolves, how a block bleeds, how the layout
frame computes its tracks → that's a **CORE** fix. Make it in `../codey/` and
export it upstream so every Codey site gets it.

CORE holds no brand values at all. That's what makes the boundary checkable:
**any diff under `codey/` is either a core fix worth exporting, or a mistake.**

## The files

| File | Holds |
| --- | --- |
| `_tokens.css` | `@theme` — Utopia type/space ramps, font tokens |
| `_globals.css` | `:root` constants, `@font-face` blocks |
| `_theme-codey.css` | default colour flavour — semantic aliases over the palette |
| `_palette-codey.css` | the shipped sample palette (0 = darkest → 9 = lightest) |
| `_palette.css` | your generated palette — `npm run palette` writes it here |
| `_overrides.css` | loaded **last** from `main.css`, so it beats everything |

**Every file here is underscore-prefixed on purpose.** CodeKit skips files
beginning with `_`, which is how a partial gets tagged as non-renderable. These
are all `@import` fragments — each carries an `@theme` block with no
`@import "tailwindcss"` of its own, so compiling one standalone emits broken CSS
into `build/`. Only `main.css` is a real entry point. Keep the prefix on
anything you add here.

Load order is set in [`../main.css`](../main.css): seeds first, core second,
generated Utopia scale third, `overrides.css` last.

## Why this directory exists

Earlier versions of Codey filed these files under `codey/` — `codey/theme.css`,
`codey/globals.css` — while also telling you never to edit `codey/`. That was
impossible to obey: a project has to set its own fonts somewhere. So the rule
got broken on the first real build, and every subsequent update risked clobbering
brand work.

Moving the seeds out is what makes "don't edit `codey/`" a rule you can actually
follow — and a rule tooling can enforce.
