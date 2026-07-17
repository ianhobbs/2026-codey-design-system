# example-project/ — consuming the Codey design system

A minimal project showing the full mechanism. In a real project these files are
the *only* things you author; the `codey/` folders come from Composer.

## How it installs

```bash
composer install      # fetches the package → vendor/, then post-install-cmd runs codey-sync
npm install           # installs Tailwind/Alpine; postinstall re-runs codey-sync (belt + braces)
npm run build:css     # compiles src/assets/css/main.css → build/assets/css/main.css
```

`codey-sync` copies the package's source into the **overwrite zones**:

| Synced (gitignored, restored on install) | Project-owned (committed) |
|------------------------------------------|---------------------------|
| `src/assets/css/codey/`                  | `src/assets/css/main.css` |
| `src/site/plugins/codey/`                | `src/assets/css/brand.css` (tier-2 override) |
| `src/assets/fonts/codey/`                | `src/assets/css/templates/*.css` (tier-3) |
|                                          | `src/site/{snippets,templates}/*` |

## The override contract (load order = precedence)

1. **Core** — `main.css` does `@import "./codey/index.css"` (tier 1).
2. **Project global** — `@import "./brand.css"` last in `main.css` (tier 2). Its
   `@theme` overrides Utopia + colour tokens (e.g. `--text-base`,
   `--color-active-1`); last declaration wins.
3. **Per-template** — `templates/{template}.css` via `css('@auto')` (tier 3),
   scoped to that template only.

Nothing in a `codey/` zone is ever hand-edited — refine the theme upstream and
`composer update`.
