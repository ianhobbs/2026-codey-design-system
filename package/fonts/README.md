# Fonts are a project override

The design system ships **no font files** and no `@font-face` rules — typefaces
are brand-specific, so bundling them would push project-specific content into the
shared package. This folder is intentionally empty of fonts and is **not synced**
into a consuming project.

## Starter template

[`brand-typography.example.css`](brand-typography.example.css) is a copyable
starter. Copy it to `src/assets/css/brand-typography.css` in your project and
uncomment what you need. Because this folder isn't synced, your copy is never
overwritten.

## How fonts work

`package/css/theme.css` names the expected families as tokens, each with a system
fallback:

```css
--body-font  --head-font  --med-font  --ital-font  --cond-font
```

`package/css/globals.css` carries no `@font-face`. Each project supplies its own:

1. Put the font files in the project's own `src/assets/fonts/` (never here).
2. Declare `@font-face` for the family names in the project's CSS — or inline the
   critical weights in the Kirby `<head>` for first-paint.
3. Optionally override the `--*-font` tokens in `brand.css` to point at entirely
   different faces.

Until a project does this, the font tokens resolve to their system fallback.
