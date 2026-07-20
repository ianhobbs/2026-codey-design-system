# Codey Design System

A versioned design system for Kirby projects. It installs as **plain source files**
into your project's `src/`, so your own build (Tailwind CLI, CodeKit, or Vite)
compiles it exactly like code you wrote yourself.

> Full detail lives in **[docs/IMPLEMENTATION-GUIDE.md](docs/IMPLEMENTATION-GUIDE.md)**.
> This page is the quickstart — enough to get a site running.

---

## Quickstart

### 1. Require the package

<sub>→ full detail: [§3.1 Consumer composer.json](docs/IMPLEMENTATION-GUIDE.md#31-add-the-consumer-composerjson)</sub>

In your project's `composer.json`:

```json
"repositories": [{ "type": "vcs", "url": "git@github.com:ianhobbsmedia/codey-design-system.git" }],
"require": { "ianhobbsmedia/codey-design-system": "^2.0" },
"scripts": {
  "codey-sync": "node vendor/ianhobbsmedia/codey-design-system/package/scripts/codey-sync.cjs",
  "post-install-cmd": ["@codey-sync"],
  "post-update-cmd":  ["@codey-sync"]
}
```

### 2. Add the front-end toolchain

<sub>→ [§2 Prerequisites](docs/IMPLEMENTATION-GUIDE.md#2-prerequisites) · [§3.2 Toolchain](docs/IMPLEMENTATION-GUIDE.md#32-add-the-front-end-toolchain-npm)</sub>

Composer can't install npm packages, so Tailwind v4 and Alpine sit beside it in
`package.json`:

```json
"scripts": {
  "build:css": "tailwindcss -i ./src/assets/css/main.css -o ./build/assets/css/main.css"
},
"devDependencies": { "tailwindcss": "^4.0.0", "@tailwindcss/cli": "^4.0.0" },
"dependencies": { "alpinejs": "^3.14.0" }
```

### 3. Install

<sub>→ [§3.3 What lands where](docs/IMPLEMENTATION-GUIDE.md#33-install-and-see-what-lands) · [§3.4 Git hygiene](docs/IMPLEMENTATION-GUIDE.md#34-git-hygiene)</sub>

```bash
composer install     # fetches the package, then syncs it into src/
npm install
```

### 4. Write your `main.css`

<sub>→ [§5 The CSS entry file](docs/IMPLEMENTATION-GUIDE.md#5-the-css-entry-file-maincss) · [§4 Override contract](docs/IMPLEMENTATION-GUIDE.md#4-the-override-contract-load-order-is-precedence)</sub>

Order matters — the core loads first, your overrides last:

```css
@import "tailwindcss";
@layer theme, base, components, utilities, bespoke;

@source "../../site/**/*.php";

@import "./codey/index.css";         /* the design system */
@import "./_brand-typography.css";   /* your fonts     (optional) */
@import "./_brand-palette.css";      /* your colours   (optional) */
@import "./_brand.css";              /* your overrides (optional) */
```

**Why the `_` prefix?** These are `@import`-ed *partials*, not standalone
stylesheets. CodeKit (and Sass-style tooling generally) skips files beginning with
`_`, so they don't get compiled into stray `build/assets/css/_brand-*.css` outputs.
That matters here because each contains an `@theme` block with no
`@import "tailwindcss"` of its own — compiled alone they'd emit broken CSS.
`main.css` is the only entry point that should compile.

### 5. Set a theme class and build

<sub>→ [§9.3 Selecting a theme](docs/IMPLEMENTATION-GUIDE.md#93-selecting-a-theme) · [§16 Build pipeline](docs/IMPLEMENTATION-GUIDE.md#16-the-build-pipeline)</sub>

Put a palette class on `<body>` (the shipped sample is `theme-codey`), then:

```bash
npm run build:css
```

---

## The one rule

<sub>→ [§4 Override contract](docs/IMPLEMENTATION-GUIDE.md#4-the-override-contract-load-order-is-precedence) · [§3.4 Git hygiene](docs/IMPLEMENTATION-GUIDE.md#34-git-hygiene)</sub>

**Never edit anything inside a `codey/` folder.** Those are replaced on every
install. Everything else in `src/` is yours and is never touched.

| Replaced on install (don't edit) | Yours (safe) |
|---|---|
| `src/assets/css/codey/` | `src/assets/css/main.css`, `_brand*.css` |
| `src/assets/js/codey/` | `src/assets/css/templates/*.css` |
| `src/site/snippets/codey/` | `src/site/snippets/*`, `templates/*` |
| `src/site/blueprints/codey/` | everything else |

To change something, override it in your own files — later imports win. Gitignore
the four `codey/` folders; `composer.lock` pins the version.

## Using it

- **Layout** — `snippet('codey/layout', ['pad' => 'large'], slots: true)` wraps a
  page; `snippet('codey/layouts', ['field' => $page->layout()])` renders a Kirby
  layout field. → [§15.1](docs/IMPLEMENTATION-GUIDE.md#151-the-layout-shell-snippetcodeylayout) ·
  [§15.4](docs/IMPLEMENTATION-GUIDE.md#154-layout-field-renderer-snippetcodeylayouts) ·
  [§10 Layout CSS](docs/IMPLEMENTATION-GUIDE.md#10-layout-codeyliblayoutcss)
- **Panel field** — `extends: codey/fields/layout` in a page blueprint.
  → [§15.5](docs/IMPLEMENTATION-GUIDE.md#155-the-layout-blueprint-field-codeyfieldslayout)
- **Colours** — semantic tokens (`--color-text`, `--link`, …) over a `0–9` scale
  where **0 is darkest**. → [§9 Colour system](docs/IMPLEMENTATION-GUIDE.md#9-colour-system-palettes-semantic-themes) ·
  [§9.4 Your own palette](docs/IMPLEMENTATION-GUIDE.md#94-making-your-own-brand-palette)
- **Type** — `--body-font`, `--head-font` (h1–h3), `--med-font` (h4–h6),
  `--bodymed-font` (`strong`), each with a matching `--*-weight`.
  → [§11 Typography](docs/IMPLEMENTATION-GUIDE.md#11-typography-codeylibtypographycss) ·
  [§11.1 Brand fonts](docs/IMPLEMENTATION-GUIDE.md#111-brand-typography-sheet-brand-typographycss-project-owned)
- **`<head>` wiring** — critical fonts, CSS load order, per-template `@auto`.
  → [§15.7](docs/IMPLEMENTATION-GUIDE.md#157-wiring-head-the-load-order-glue) ·
  [§13 Per-template CSS](docs/IMPLEMENTATION-GUIDE.md#13-per-template-defaults-codeytemplates)

## Tools

> **Where they live:** the scripts are **not synced into `src/`** — they stay in
> `vendor/ianhobbsmedia/codey-design-system/package/scripts/`. Since `vendor/` is
> gitignored, **your editor's search will skip it by default**, so the tools look
> like they're missing. They're not; only their *output* lands in your project.

Add these once to your project's `package.json` and you never need the path again:

```json
"scripts": {
  "codey:sync":    "node vendor/ianhobbsmedia/codey-design-system/package/scripts/codey-sync.cjs",
  "codey:palette": "node vendor/ianhobbsmedia/codey-design-system/package/scripts/brand-palette.cjs"
}
```

```bash
npm run codey:palette            # prints all options
npm run codey:sync               # re-sync (Composer also runs this on install)

# generate a brand palette in OKLCH from two poles
npm run codey:palette -- \
  --dark "#0f151b" --light "#eef6fe" --mid "#1fa7f3" \
  --half 1.5,2.5 --scope ".theme-acme" \
  --out src/assets/css/_brand-palette.css
```

Note the `--` before the flags: npm needs it to pass arguments through to the
script. Both tools are plain Node with **no dependencies**.
<sub>→ [§9.0 Generating a brand palette](docs/IMPLEMENTATION-GUIDE.md#90-generating-a-brand-palette)</sub>

## Docs

| | |
|---|---|
| [IMPLEMENTATION-GUIDE](docs/IMPLEMENTATION-GUIDE.md) | The full manual — every token, element and override, with reasoning. **Start here.** |
| [DESIGN-SYSTEM](docs/DESIGN-SYSTEM.md) | How the sync/override mechanism works |
| [ARCHITECTURE](docs/ARCHITECTURE.md) | Why the system is shaped this way |
| [ROADMAP](docs/ROADMAP.md) | What's done and what's next |
| [Appendix A](docs/IMPLEMENTATION-GUIDE.md#appendix-a-token-quick-reference) | **Token quick reference** — every token on one page |
| [Appendix B](docs/IMPLEMENTATION-GUIDE.md#appendix-b-layout-attributes) | Layout attributes (`data-layout`, `data-pad`) |

Upgrading from 1.x? See **[§17.1 Migrating to 2.0](docs/IMPLEMENTATION-GUIDE.md#171-migrating-to-20-breaking)** — the colour scale was reversed.
New project? Run the **[§18 fresh-project checklist](docs/IMPLEMENTATION-GUIDE.md#18-fresh-project-checklist)**.

---

## Maintaining this repo

The repo root *is* the Composer package. The payload lives under `package/`;
`.gitattributes` `export-ignore` trims the dist archive to that payload plus the
manifest and this README, so it publishes directly with no subtree split.

```
composer.json            root manifest — bin → package/scripts/codey-sync.cjs
package/
  VERSION                semver (stamped into src/.codey-version on sync)
  codey-sync.json        source→dest zone map (the clobber-safety contract)
  scripts/               codey-sync.cjs · brand-palette.cjs
  css/    → src/assets/css/codey/    tokens, palette, theme, lib/, index.css
  js/     → src/assets/js/codey/
  kirby/snippets/   → src/site/snippets/codey/
  kirby/blueprints/ → src/site/blueprints/codey/
  fonts/                 guidance + brand-typography starter (not synced)
docs/
```

Release: bump `package/VERSION`, `package/codey-sync.json` and `package.json`
together, commit, then tag `vX.Y.Z` — Composer resolves the tag, not the manifest.
