# Architecture

## Why this shape

The source project (`codey-new-2025`) works but is too entangled to reuse: the
design system, the build pipeline, and one site's content all live in the same
tree. Copying it to start a new site drags all of that coupling along.

This repo separates the **system** from the **implementation**. The system is a
single versioned package; projects consume it. The inversion — projects depend on
the system, the system depends on nothing project-specific — is the whole point.

## Delivery: Composer versions, a script places

The theme is **not** referenced in place from `vendor/`/`node_modules/`. It must
land as plain source under a project's `src/` so the project's own pipeline
(CodeKit, Tailwind CLI, or Vite) compiles it exactly like the project's own
files — honouring the `src/ → build/` (or `public/`) mirror.

Two mechanisms, each doing one job:

- **Composer** fetches and semver-pins the package into `vendor/`. This is the
  *version* channel (`composer.lock` records the exact release).
- **`package/scripts/codey-sync.cjs`** (run by Composer `post-install-cmd` / npm
  `postinstall`) copies the package source from `vendor/` into `src/`. This is
  the *placement* channel.
- **npm** lives beside Composer at root purely for the build toolchain (Tailwind,
  Alpine) — Composer can't fetch npm packages.

Git subtree/submodule were rejected: subtree loses explicit version pinning;
submodule drops a nested `.git` into `src/` that fights CodeKit's watcher and
needs an init step. Composer + copy script keeps `src/` as plain files with real
semver.

## Layers (what the package ships)

```
┌──────────────────────────────────────────────┐
│ consuming project  (own main.css, brand.css,  │  ← authors only its own files
│                     templates, snippets)      │
├──────────────────────────────────────────────┤
│ Kirby plugin  (layout shell, header/footer,   │  ← package/kirby → src/site/plugins/codey
│                layout-field renderer, layout.yml) │
├──────────────────────────────────────────────┤
│ CSS core  (layout frame, typography, elements)│  ← package/css/lib → src/assets/css/codey
├──────────────────────────────────────────────┤
│ Colour system  (palettes + semantic themes)   │  ← package/css/{palettes,themes}
├──────────────────────────────────────────────┤
│ Tokens  (@theme Utopia type/space, globals)   │  ← package/css/{theme,globals}.css
└──────────────────────────────────────────────┘
```

Dependencies point downward only. A rule references the tokens below it; it never
reaches up into a consumer or sideways into another project.

## Front-end / Kirby split

Kirby projects are half PHP, half front-end, so the package ships both, and both
ride the same sync:

- **CSS/tokens/fonts** → `src/assets/css/codey/` + `src/assets/fonts/codey/`,
  compiled in place by the project's Tailwind pipeline.
- **Kirby plugin** → `src/site/plugins/codey/`, mirrored to `build/` by CodeKit
  and loaded by Kirby. A project overrides any snippet/template/blueprint *by
  name* (Kirby resolves site-level over plugin-registered).

## The invariant that prevents re-complexification

Each item that enters the package must pass two tests:

1. **No hardcoded value a token could express.** Colours, spacing, and type sizes
   come from the `@theme` tokens / palettes. This is what makes a rule portable
   across brands — override the tokens, keep the rule.
2. **No project-specific content.** No client logos, licenses/keys, decorative
   SVG backgrounds, or assumptions about one site's page model. If it only makes
   sense for one project, it stays in that project.

Anything failing either test is *project*, not *system*. On extraction, coupled
pieces were decoupled (font paths repointed at the synced zone, decorative leaf
SVGs and products-block rules stripped to commented placeholders, project blocks
dropped from `layout.yml`).

## Opinionated manifest

`package/css/index.css` lists the core (always on) and the **optional components
commented out**. A project uncomments only the ones whose markup it uses — no
accordion markup means the accordion line stays commented and ships zero bytes.
The optional lib files are token *seeds* (generically useful custom properties)
with guidance comments, not full components.
