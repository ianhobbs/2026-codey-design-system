# Codey Design System

A universal, versioned design system for Kirby projects. It exists so a new
Codey site starts from a shared, battle-tested foundation instead of copy-paste
archaeology from the last project.

The system is **consumed**, not cloned. Individual sites pull it in as versioned
packages; they never fork it.

## Model

The repo is a monorepo of independently consumable packages, layered by how
often each changes and how it ships:

| Layer | Package | Ships as | Depends on |
|-------|---------|----------|------------|
| **Tokens** | `packages/tokens` (`@codey/tokens`) | CSS custom properties (+ JSON) via npm | — |
| **CSS primitives** | `packages/css` (`@codey/css`) | Compiled CSS bundle via npm | tokens |
| **Assets** | `packages/assets` (`@codey/assets`) | Fonts + icons via npm | — |
| **Kirby** | `packages/kirby` (`ianhobbsmedia/codey-design-system`) | Kirby plugin via Composer | — |
| **Starterkit** | `starterkit/` | Template to copy for a new site | all of the above |

The rule that keeps it from collapsing back into complexity: **each layer only
references the layer below it, never hardcoded values, and never
project-specific content.** A CSS primitive references tokens; it does not bake
in a colour. A Kirby snippet is a reusable shape; it does not assume a
particular site's content.

## Distribution

Two channels, because Kirby spans PHP and the front-end:

- **Composer** — `composer require ianhobbsmedia/codey-design-system` installs
  the Kirby plugin (snippets, blocks, blueprints, templates, models).
- **npm** — `npm install @codey/tokens @codey/css @codey/assets` installs the
  front-end layer.

Both are versioned with semver so a site pins a major version and upgrades
deliberately.

## Spinning up a new site

1. Copy `starterkit/` to a new project folder.
2. `composer require ianhobbsmedia/codey-design-system`
3. `npm install @codey/tokens @codey/css @codey/assets`
4. Wire the CSS bundle + fonts into your template head; override tokens per brand.

## Repo layout

```
packages/
  tokens/     colour palettes, spacing, Utopia fluid type scale
  css/        grid, containers, off-screen/off-page, effects, elements, type, forms
  assets/     fonts, icons
  kirby/      Kirby plugin: snippets, blocks, blueprints, templates, models
starterkit/   minimal runnable Kirby site that consumes all packages
docs/         architecture notes + the extraction roadmap
build/        shared build tooling (Stylus/CodeKit pipeline)
```

## Status

Scaffold stage. Structure, manifests, and the plugin entry point are in place;
the packages are not yet populated. See **[docs/ROADMAP.md](docs/ROADMAP.md)**
for the phased extraction plan and the exact source files each package pulls
from.
