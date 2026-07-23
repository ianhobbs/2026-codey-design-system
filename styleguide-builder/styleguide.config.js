/**
 * styleguide.config.js
 *
 * Configured for Codey.net.au — Kirby 5 + Tailwind CSS v4.
 *
 * NOTE: This project is Tailwind v4 (CSS-first). Design tokens live in the
 * `@theme {}` block of main.css and in the colour palette files under
 * assets/css/themes/ — NOT in a tailwind.config.js. The v4 extractor
 * (extractor/extract-v4.js) reads those sources directly.
 *
 * Paths are relative to this config file (the styleguide-builder/ folder).
 */

module.exports = {

  // ─── Project info ────────────────────────────────────────────────────────
  project: {
    name:    'Codey.net.au',
    version: '1.0.0',
    url:     'https://codey.net.au',
  },

  // ─── Source paths (relative to this config file) ─────────────────────────
  paths: {
    // Tailwind v4 @theme block — type, spacing, leading, fonts, radii.
    // In the new structure the @theme lives in the codey core, not main.css.
    themeCss:     '../src/assets/css/codey/theme.css',

    // Colour palette files (_palette-*.css) — hex colour scales
    palettesDir:  '../src/assets/css/codey/palettes',

    // Layout field blueprint — grid patterns + layout themes catalog
    layoutBlueprint: '../src/site/blueprints/fields/layout.yml',

    // PHP templates / snippets (scanned for data-component blocks, if any)
    templates:    '../src/site/templates/**/*.php',
    partials:     '../src/site/snippets/**/*.php',

    // Compiled CSS (fallback only — not used by the v4 extractor)
    compiledCss:  '../build/assets/css/main.css',

    // Output — static style guide lands here (servable at /styleguide/)
    output:       '../build/styleguide',
  },

  // ─── Extraction options ───────────────────────────────────────────────────
  extract: {
    // Default Tailwind v4 breakpoints to document (none are overridden in
    // @theme, so we surface the framework defaults the project relies on).
    defaultBreakpoints: {
      sm: '40rem',
      md: '48rem',
      lg: '64rem',
      xl: '80rem',
      '2xl': '96rem',
    },
  },

  // ─── Style guide display options ──────────────────────────────────────────
  guide: {
    sections: ['colors', 'typography', 'spacing', 'layouts', 'codey-arch', 'breakpoints'],
    groupColorsByTheme: true,
  },

  // ─── Codey Architecture reference ─────────────────────────────────────────
  codeyArch: {
    docPath: '../docs/codey-arch.md',
  },

};
