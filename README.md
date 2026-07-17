# Codey Design System

A versioned, proprietary design system for Codey Kirby projects, authored once and
synced into each project's `src/` so the project's own build (Tailwind CLI /
CodeKit / Vite) compiles it in place.

Delivered as a **Composer package + a copy script**: Composer versions and fetches
it into `vendor/`; a `post-install` script copies its source into `src/`. npm lives
beside Composer at the project root for the build toolchain (Tailwind, Alpine).

## This repo *is* the package

The repo root is the Composer package (`ianhobbsmedia/codey-design-system`). The
payload lives under `package/`; `.gitattributes` `export-ignore` trims the Composer
dist archive to just that payload + README + manifest, so no subtree split is
needed — publish this repo directly.

```
composer.json              root manifest — name + bin → package/scripts/codey-sync.cjs
.gitattributes             export-ignore rules that keep the dist archive lean
package/                   the payload synced into a consuming project
  VERSION                  semver (also echoed to src/.codey-version on sync)
  codey-sync.json          source→dest zone map (clobber-safety contract)
  scripts/codey-sync.cjs   the copy script (Composer post-install / npm postinstall)
  css/     → src/assets/css/codey/
    theme.css              @theme Utopia type/space tokens
    globals.css            :root globals + @font-face
    palettes/_codey.css    raw palette
    themes/theme-codey.css semantic colour map
    lib/                   layout, typography, elements (+ form/accordion/transitions/cards seeds, opt-in)
    templates/             core per-template defaults (note.css)
    index.css              opinionated manifest (core on, optional commented)
  kirby/   → src/site/plugins/codey/   index.php + snippets/ + blueprints/ + templates/
  fonts/   → src/assets/fonts/codey/   (licensed core fonts — pending)
docs/                      ARCHITECTURE · DESIGN-SYSTEM · IMPLEMENTATION-GUIDE · ROADMAP
```

## How it works (see docs/DESIGN-SYSTEM.md)

- **Zone boundary** — the sync writes only into `src/**/codey/`; everything else in
  `src/` is project-owned and never touched. Synced zones are gitignored and
  restored on install, like `vendor/`.
- **Override contract** — `main.css` imports the core first, then the project's
  `brand.css` last (tier-2 token overrides win), plus per-template `@auto` CSS
  (tier 3). Kirby snippets/templates/blueprints override by name.
- **Opinionated manifest** — optional components ship commented-out in `index.css`;
  uncomment only the markup you use.

## Install (consuming project)

```json
"repositories": [{ "type": "vcs", "url": "git@github.com:ianhobbsmedia/codey-design-system.git" }],
"require": { "ianhobbsmedia/codey-design-system": "^1.0" },
"scripts": {
  "codey-sync": "node vendor/ianhobbsmedia/codey-design-system/package/scripts/codey-sync.cjs",
  "post-install-cmd": ["@codey-sync"],
  "post-update-cmd":  ["@codey-sync"]
}
```

`composer install` fetches the package and the script syncs its source into
`src/assets/css/codey`, `src/site/plugins/codey`, `src/assets/fonts/codey`.

## Status

Core extracted and compile-verified: `@theme` Utopia tokens, `:root` globals, the
codey colour system, the two-axis layout frame + `.blocks-grid`, typographic base,
aspect-ratio elements, four opt-in component token seeds, and the Kirby layout
plugin. Pending: the prose component, full optional-component CSS, bundled
fonts/icons, generator tools, and a live Kirby run. See
[docs/ROADMAP.md](docs/ROADMAP.md).
