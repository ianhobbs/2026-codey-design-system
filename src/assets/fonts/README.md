# Fonts are a project override

Codey ships **no font files** and no `@font-face` rules — typefaces are
brand-specific, so bundling them would push project content into the shared system.

Fonts belong to the **SEED** tier: `src/assets/css/brand/`. Codey seeds those files
once at clone time and never touches them again, so anything you put there is safe
from updates and is never exported back upstream. See
[`../css/brand/README.md`](../css/brand/README.md).

## How fonts work

`brand/_tokens.css` names the expected families as tokens, each with a system
fallback:

```css
--font-body  --font-head  --font-subhead  --font-italic  --font-mono
```

`brand/_globals.css` is where the matching `@font-face` blocks go. Each project:

1. Puts its font files in `src/assets/fonts/` (they're gitignored and rsynced to
   the server separately — see `npm run deploy:push`).
2. Declares `@font-face` for those family names in `brand/_globals.css` — or inlines
   the critical weights in the Kirby `<head>` for first paint.
3. Optionally repoints the `--*-font` tokens in `brand/_tokens.css` at entirely
   different faces.

Until a project does this, the font tokens resolve to their system fallback.

## Starter template

[`brand-typography.example.css`](brand-typography.example.css) is a copyable
starter — lift its `@font-face` blocks into `brand/_globals.css` and its token
overrides into `brand/_tokens.css`.

> Earlier revisions told you to copy it to `src/assets/css/_brand-typography.css`
> and import that as a separate file. That path no longer exists; the SEED tier
> replaced it, and there is no longer any reason for a separate typography sheet —
> `brand/` is already yours.

## Variable vs static faces

Codey's defaults assume **pre-weighted (static)** faces: each cut is its own file,
so the weight token stays `400` and the family does the work. A **variable**-font
project keeps one family and raises the axis instead, in `brand/_tokens.css`:

```css
--font-body-medium: var(--font-body);  --weight-body-medium: 500;
--font-subhead:     var(--font-body);  --weight-subhead:     500;
```

Why `400` for a medium cut: the typographer builds the weight into the stroke
outlines, so `400` renders the face exactly as drawn. Asking for `500`/`600` makes
the browser synthesise a faux-bold on top of an already-medium face and smear the
letterforms.
