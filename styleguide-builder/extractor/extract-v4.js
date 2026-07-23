/**
 * extract-v4.js
 *
 * Tailwind CSS v4 token extractor for Codey.net.au.
 *
 * Reads design tokens directly from the CSS source (no tailwind.config.js):
 *   • @theme {} block in main.css  → type scale, spacing, leading, fonts, radii
 *   • themes/_palette-*.css        → hex colour scales, grouped per theme
 *
 *      Writes a ready-to-render merged.json into extractor/tokens/.
 */

const fs   = require('fs');
const path = require('path');
const config = require('../styleguide.config.js');

const OUT_DIR = path.join(__dirname, 'tokens');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

const resolveP = rel => path.resolve(__dirname, '..', rel);

// ─── Helpers ─────────────────────────────────────────────────────────────────

/** Pull every `--name: value;` declaration out of a CSS string. */
function declarations(css) {
  const out = [];
  const re = /--([\w-]+)\s*:\s*([^;]+);/g;
  let m;
  while ((m = re.exec(css)) !== null) {
    out.push({ name: m[1].trim(), value: m[2].trim() });
  }
  return out;
}

function titleCase(s) {
  return s.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// ─── @theme block ────────────────────────────────────────────────────────────

function parseThemeBlock(cssPath) {
  if (!fs.existsSync(cssPath)) {
    console.warn(`  ⚠ theme CSS not found: ${cssPath}`);
    return { size: {}, family: {}, weight: {}, spacing: {}, leading: {}, radii: {}, breakpoints: {} };
  }

  const css   = fs.readFileSync(cssPath, 'utf8');
  const block = css.match(/@theme\s*\{([\s\S]*?)\n\}/);
  if (!block) {
    console.warn('  ⚠ no @theme {} block found in', cssPath);
    return { size: {}, family: {}, weight: {}, spacing: {}, leading: {}, radii: {}, breakpoints: {} };
  }

  const size = {}, family = {}, weight = {}, spacing = {}, leading = {}, radii = {}, breakpoints = {};

  // Resolve var(--x) references (e.g. font families now read "Gradual",
  // var(--font-fallback)) against the full token map, so the guide shows the
  // real stack rather than an unresolved — and CSS-invalid — var().
  const decls = declarations(block[1]);
  const vars  = {};
  for (const d of decls) vars[d.name] = d.value;
  const resolveVars = (val, depth = 0) =>
    depth > 6 ? val : val.replace(/var\(\s*--([\w-]+)\s*(?:,[^)]*)?\)/g,
      (_, n) => (vars[n] != null ? resolveVars(vars[n], depth + 1) : `var(--${n})`));

  for (const { name, value } of decls) {
    if (value === 'initial') continue;                 // skip Tailwind resets

    if (/^text(-|$)/.test(name))            size[name] = value;
    else if (/^spacing-/.test(name))        spacing[name.replace(/^spacing-/, '')] = value;
    else if (/^breakpoint-/.test(name))     breakpoints[name.replace(/^breakpoint-/, '')] = value;
    else if (/-font$/.test(name))           family[name.replace(/-font$/, '')] = resolveVars(value);
    else if (/^font-weight-/.test(name))    weight[name.replace(/^font-weight-/, '')] = value;
    else if (/^leading-/.test(name))        leading[name.replace(/^leading-/, '')] = value;
    else if (/radius/.test(name))           radii[name] = value;
  }

  return { size, family, weight, spacing, leading, radii, breakpoints };
}

// ─── Palette files ───────────────────────────────────────────────────────────

function parsePalettes(dir) {
  if (!fs.existsSync(dir)) {
    console.warn(`  ⚠ palettes dir not found: ${dir}`);
    return [];
  }

  const files = fs.readdirSync(dir)
    .filter(f => /^_?palette-.*\.css$/i.test(f))
    .sort();

  const themes = [];

  for (const file of files) {
    const theme = path.basename(file, '.css').replace(/^_?palette-/i, '');
    const css   = fs.readFileSync(path.join(dir, file), 'utf8');

    // Concrete colour declarations — hex OR a modern colour function (oklch,
    // lab, rgb, hsl, …). The Codey palettes are generated in OKLCH, so hex-only
    // matching would miss every swatch. var() chains and `initial` resets are
    // still skipped; the first concrete value per token wins.
    const colors = {};
    const re = /--([\w-]+)\s*:\s*(#[0-9a-fA-F]{3,8}|(?:oklch|oklab|rgb|rgba|hsl|hsla|lab|lch|hwb|color)\([^)]*\))/gi;
    let m;
    while ((m = re.exec(css)) !== null) {
      const [, name, value] = m;
      const v = value.startsWith('#') ? value.toLowerCase() : value.trim();
      if (!colors[name]) colors[name] = v;
    }

    if (Object.keys(colors).length) {
      themes.push({ theme, label: titleCase(theme), colors, source: file });
    }
  }

  return themes;
}

// ─── Semantic / brand :root tokens ─────────────────────────────────────────

/**
 * Brand + semantic colour tokens live in `:root` (main.css → accents;
 * compiled lib/theme.css → caramel / black / white / report status), NOT in the
 * palette files. Surface them as their own colour group. Any CSS colour value
 * (hex, oklch, rgb, lab…) is kept; non-colours (e.g. --note-width) are skipped.
 */
function parseRootColors(cssPaths) {
  const isColorVal = v =>
    /^#[0-9a-fA-F]{3,8}$/.test(v) ||
    /^(oklch|oklab|rgb|rgba|hsl|hsla|lab|lch|hwb|color)\(/i.test(v);

  const colors = {};
  for (const p of cssPaths) {
    if (!fs.existsSync(p)) continue;
    const css = fs.readFileSync(p, 'utf8');
    const re  = /:root\s*\{([\s\S]*?)\n\}/g;
    let block;
    while ((block = re.exec(css)) !== null) {
      for (const { name, value } of declarations(block[1])) {
        if (isColorVal(value) && !colors[name]) colors[name] = value;
      }
    }
  }

  return Object.keys(colors).length
    ? { theme: 'semantic', label: 'Semantic / Brand', colors }
    : null;
}

// ─── Layout blueprint ──────────────────────────────────────────────────────

/**
 * Reads the layout field blueprint (fields/layout.yml) and returns the catalog
 * of available grid patterns + layout themes. Lightweight regex parse — no YAML
 * dependency — matching this builder's zero-dependency token extractors.
 */
function parseLayouts(yamlPath) {
  if (!fs.existsSync(yamlPath)) {
    console.warn(`  ⚠ layout blueprint not found: ${yamlPath}`);
    return { patterns: [], themes: [] };
  }

  const yaml = fs.readFileSync(yamlPath, 'utf8');

  // Grid patterns: the top-level `layouts:` list of quoted width specs.
  // Captured up to the next top-level key (e.g. `fieldsets:`).
  const patterns = [];
  const block = yaml.match(/\nlayouts:\n([\s\S]*?)(?=\n\S)/);
  if (block) {
    const re = /-\s*"([^"]+)"/g;
    let m;
    while ((m = re.exec(block[1])) !== null) patterns.push(m[1].trim());
  }

  // Layout themes: value/text pairs under settings.fields.theme.options.
  // De-duped by value (the blueprint lists card-blocks twice).
  const themes = [];
  const seen = new Set();
  const re = /-\s*value:\s*(\S+)\s*\n\s*text:\s*(.+)/g;
  let m;
  while ((m = re.exec(yaml)) !== null) {
    const value = m[1].trim();
    if (seen.has(value)) continue;
    seen.add(value);
    themes.push({ value, text: m[2].trim() });
  }

  return { patterns, themes };
}

// ─── Main ─────────────────────────────────────────────────────────────────────

function run() {
  console.log('\n🎨 Extracting Tailwind v4 tokens...');

  const theme    = parseThemeBlock(resolveP(config.paths.themeCss));
  const palettes = parsePalettes(resolveP(config.paths.palettesDir));
  const layouts  = parseLayouts(resolveP(config.paths.layoutBlueprint));

  // Semantic / brand :root tokens (accents in main.css, status/neutrals in
  // compiled lib/theme.css) — prepended so they lead the colour section.
  const themeCssPath = resolveP(config.paths.themeCss);
  const semantic = parseRootColors([
    themeCssPath,
    path.join(path.dirname(themeCssPath), 'lib', 'theme.css'),
  ]);
  const themes = semantic ? [semantic, ...palettes] : palettes;

  // Breakpoints: none are customised in @theme, so document the v4 defaults
  // the project relies on (unless @theme overrides some).
  const breakpoints = Object.keys(theme.breakpoints).length
    ? theme.breakpoints
    : (config.extract.defaultBreakpoints || {});

  const colorCount = themes.reduce((n, t) => n + Object.keys(t.colors).length, 0);

  const merged = {
    project: {
      name:    config.project.name,
      version: config.project.version,
      url:     config.project.url,
      generatedAt: new Date().toLocaleDateString('en-AU', {
        day: '2-digit', month: 'long', year: 'numeric',
      }),
    },
    colorThemes: themes.map(({ theme, label, colors }) => ({ theme, label, colors })),
    colorCount,
    fonts: {
      family: theme.family,
      size:   theme.size,
      weight: theme.weight,
    },
    spacing:     theme.spacing,
    breakpoints,
    leading:     theme.leading,
    radii:       theme.radii,
    layouts,
    _meta: {
      source:      'tailwind-v4',
      extractedAt: new Date().toISOString(),
      themes:      themes.map(t => t.theme),
      counts: {
        colorThemes: themes.length,
        colors:      colorCount,
        fontSizes:   Object.keys(theme.size).length,
        fontFamilies:Object.keys(theme.family).length,
        spacing:     Object.keys(theme.spacing).length,
        breakpoints: Object.keys(breakpoints).length,
        layoutPatterns: layouts.patterns.length,
        layoutThemes:   layouts.themes.length,
      },
    },
  };

  const out = path.join(OUT_DIR, 'merged.json');
  fs.writeFileSync(out, JSON.stringify(merged, null, 2));

  console.log(`  ✓ Tokens written to ${out}`);
  console.log(`    Colour themes: ${themes.length} (${themes.map(t => `${t.theme}:${Object.keys(t.colors).length}`).join(', ')})`);
  console.log(`    Total colours: ${colorCount}`);
  console.log(`    Font sizes:    ${Object.keys(theme.size).length}`);
  console.log(`    Font families: ${Object.keys(theme.family).length}`);
  console.log(`    Spacing:       ${Object.keys(theme.spacing).length}`);
  console.log(`    Breakpoints:   ${Object.keys(breakpoints).length}`);
  console.log(`    Layouts:       ${layouts.patterns.length} patterns, ${layouts.themes.length} themes`);
}

run();
