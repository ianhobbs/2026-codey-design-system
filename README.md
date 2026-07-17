# Codey Design System

A versioned design system for Codey Kirby projects, authored once and synced into
each project's `src/` so the project's own build (CodeKit / Tailwind CLI / Vite)
compiles it — without fighting the `src/ → build/` mirror.

It is delivered as a **Composer package + a copy script**: Composer versions and
fetches it into `vendor/`; a `post-install` script copies its source into `src/`.
npm lives beside Composer at root for the build toolchain (Tailwind, Alpine).

## Layout

```
package/                       the canonical theme — versioned, `composer require`d
  composer.json / VERSION      library package + semver
  codey-sync.json              source→dest zone map (clobber-safety contract)
  scripts/codey-sync.js        the copy script (Composer post-install / npm postinstall)
  css/            → src/assets/css/codey/
    theme.css                  @theme Utopia type/space tokens
    globals.css                :root globals + @font-face
    palettes/                  raw palettes: _codey, _caramel, _users(template)
    themes/                    semantic colour maps: theme-codey, theme-caramel
    lib/                       layout, typography, elements (+ form/accordion/
                               transitions/cards token seeds, opt-in)
    index.css                  the opinionated manifest (core on, optional commented)
  kirby/          → src/site/plugins/codey/
    index.php                  registers codey/* snippets + the layout blueprint
    snippets/                  layout (shell), header, footer, layouts (renderer), card
    blueprints/fields/         layout.yml
    templates/                 default.php (example)
  fonts/          → src/assets/fonts/codey/   (licensed core fonts — pending)
example-project/               a consuming project demonstrating the whole mechanism
docs/                          ARCHITECTURE · DESIGN-SYSTEM · ROADMAP
```

## How it works (see docs/DESIGN-SYSTEM.md for detail)

- **Zone boundary** — the sync writes only into `src/**/codey/`; everything else
  in `src/` is project-owned and never touched. Synced zones are gitignored and
  restored on install, like `vendor/`.
- **Override contract** — `main.css` imports the core first, then the project's
  `brand.css` last (tier-2 token overrides win), plus per-template `@auto` CSS
  (tier 3). Kirby snippets/templates/blueprints override by name.
- **Opinionated manifest** — optional components ship commented-out in
  `index.css`; uncomment only the markup you use.

## Extracted so far

Populated and compile-verified: `@theme` Utopia tokens, `:root` globals, the
colour system (palettes + semantic themes), the two-axis layout frame +
`.blocks-grid`, the typographic base, aspect-ratio elements, four opt-in
component token seeds, and the Kirby layout plugin (shell / header / footer /
layout-field renderer / `layout.yml`). See [docs/ROADMAP.md](docs/ROADMAP.md) for
what remains (prose component, full optional-component CSS, fonts/icons, tools,
and a real Kirby run).

## Demonstrated

`example-project/` + the sync script prove it end-to-end: a re-sync restores a
tampered vendored file while project overrides survive, and overridden tokens
(`--text-base`, `--color-active-1`) resolve to the project's brand values.
