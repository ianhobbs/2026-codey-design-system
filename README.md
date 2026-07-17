# Codey Design System

A versioned design system for Codey Kirby projects, authored once and synced into
each project's `src/` so the project's own build (CodeKit / Tailwind CLI / Vite)
compiles it — without fighting the `src/ → build/` mirror.

It is delivered as a **Composer package + a copy script**: Composer versions and
fetches it into `vendor/`; a `post-install` script copies its source into `src/`.
npm lives beside Composer at root for the build toolchain (Tailwind, Alpine).

## Layout

```
package/          the canonical theme — versioned, `composer require`d
  css/            → src/assets/css/codey/   (core + @theme tokens)
  kirby/          → src/site/plugins/codey/ (plugin: snippets/blocks/blueprints)
  fonts/          → src/assets/fonts/codey/
  scripts/codey-sync.js   the copy script (Composer post-install / npm postinstall)
  codey-sync.json         source→dest zone map (the clobber-safety contract)
example-project/  a consuming project demonstrating the whole mechanism
docs/DESIGN-SYSTEM.md     the decision trail, zone boundary, override contract
```

## How it works (see docs/DESIGN-SYSTEM.md for detail)

- **Zone boundary** — the sync writes only into `src/**/codey/`; everything else
  in `src/` is project-owned and never touched. Synced zones are gitignored and
  restored on install, like `vendor/`.
- **Override contract** — `main.css` imports the core first, then the project's
  `brand.css` last (tier 2 token overrides win), plus per-template `@auto` CSS
  (tier 3). Kirby snippets/templates override by name.

## Demonstrated

`example-project/` + the sync script prove it end-to-end: a re-sync restores a
tampered vendored file while project overrides survive, and overridden tokens
(`--text-base`, `--color-accent`) resolve to the project's brand values.
