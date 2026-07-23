/**
 * build-guide.js
 * 
 * Reads merged tokens.json and generates a self-contained static style guide
 * at the configured output path. No framework dependencies — pure HTML/CSS/JS.
 */

const fs       = require('fs');
const path     = require('path');
const mustache = require('mustache');
const config   = require('../styleguide.config.js');

const TOKEN_FILE = path.join(__dirname, '..', 'extractor', 'tokens', 'merged.json');
const TMPL_DIR   = path.join(__dirname, 'templates');
const OUT_DIR    = path.resolve(__dirname, '..', config.paths.output);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function readTokens() {
  if (!fs.existsSync(TOKEN_FILE)) {
    console.error('  ✗ merged.json not found. Run: npm run extract:all && npm run merge');
    process.exit(1);
  }
  return JSON.parse(fs.readFileSync(TOKEN_FILE, 'utf8'));
}

/**
 * Naive luminance check to pick black/white label text
 * Works with hex colours
 */
function textColorForBg(hex) {
  const clean = hex.replace('#', '');
  if (clean.length !== 6) return '#000000';
  const r = parseInt(clean.slice(0, 2), 16);
  const g = parseInt(clean.slice(2, 4), 16);
  const b = parseInt(clean.slice(4, 6), 16);
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return luminance > 0.5 ? '#111111' : '#ffffff';
}

function isHex(val) {
  return /^#[0-9a-fA-F]{3,8}$/.test((val || '').trim());
}

/** Any renderable CSS colour — hex OR a colour function (oklch/rgb/lab/…). */
function isColor(val) {
  const v = (val || '').trim();
  return isHex(v) || /^(oklch|oklab|rgb|rgba|hsl|hsla|lab|lch|hwb|color)\(/i.test(v);
}

/**
 * First numeric value in a string — handles clamp()/min()/calc() so tokens
 * like `clamp(1.53rem, ...)` sort by their base size rather than NaN.
 */
function firstNum(val) {
  const m = String(val).match(/-?\d*\.?\d+/);
  return m ? parseFloat(m[0]) : Infinity;
}

/**
 * Colours grouped per theme (caramel / codey / users), each with its
 * own sorted swatch list. Hex only — non-hex semantic tokens are skipped
 * upstream by the extractor.
 */
function prepareColorThemes(themes) {
  return (themes || []).map(t => ({
    theme: t.theme,
    label: t.label,
    count: Object.keys(t.colors || {}).length,
    swatches: Object.entries(t.colors || {})
      .map(([name, value]) => ({
        name,
        value,
        isHex:     isHex(value),
        isColor:   isColor(value),
        textColor: isHex(value) ? textColorForBg(value) : '#111111',
      }))
      .sort((a, b) => a.name.localeCompare(b.name)),
  }));
}

function prepareFonts(fonts) {
  const families = Object.entries(fonts?.family || {}).map(([name, stack]) => ({
    name,
    stack: Array.isArray(stack) ? stack.join(', ') : stack,
    label: name.replace(/-/g, ' '),
    sample: 'how quickly daft jumping zebras vex!',
  }));

  const sizes = Object.entries(fonts?.size || {}).map(([name, val]) => ({
    name,
    value: Array.isArray(val) ? val[0] : val,
    label: name,
  })).sort((a, b) => firstNum(a.value) - firstNum(b.value));

  return { families, sizes };
}

function prepareSpacing(raw) {
  return Object.entries(raw || {})
    .map(([name, value]) => ({ name, value, label: name.replace(/-/g, ' ') }))
    .sort((a, b) => firstNum(a.value) - firstNum(b.value))
    .slice(0, 60); // cap to avoid huge tables
}

function prepareComponents(raw) {
  return Object.values(raw || {})
    .sort((a, b) => a.name.localeCompare(b.name))
    .map(comp => ({
      ...comp,
      hasElements:  comp.elements?.length > 0,
      hasModifiers: comp.modifiers?.length > 0,
      elementList:  (comp.elements || []).map(e => `${comp.name}__${e}`).join(', '),
      modifierList: (comp.modifiers || []).map(m => `${comp.name}--${m}`).join(', '),
      sourceList:   (comp.sources || []).join(', '),
      badgeClass:   comp.type === 'stylus-only' ? 'badge--warning' :
                    comp.type === 'data-component' ? 'badge--info' : 'badge--ok',
      badgeLabel:   comp.type === 'stylus-only' ? 'CSS only' :
                    comp.type === 'data-component' ? 'data-attr' : 'BEM',
    }));
}

function prepareBreakpoints(raw) {
  return Object.entries(raw || {}).map(([name, value]) => ({ name, value }));
}

/**
 * Layout catalog from the field blueprint. Each width spec (e.g. "1/6, 4/6, 1/6")
 * becomes a row of proportional columns — the fraction value drives flex-grow so
 * the preview boxes render at their true relative widths.
 */
function prepareLayouts(raw) {
  const patterns = (raw?.patterns || []).map(spec => {
    const cols = spec.split(',').map(s => s.trim()).map(frac => {
      const [a, b] = frac.split('/').map(Number);
      const grow = b ? a / b : 1;
      return { label: frac, grow: +grow.toFixed(4) };
    });
    return { spec, count: cols.length, cols };
  });

  const themes = (raw?.themes || []).map(t => ({ value: t.value, text: t.text }));

  return {
    patterns,
    themes,
    patternCount: patterns.length,
    themeCount:   themes.length,
  };
}

/**
 * Parse codey-arch.md to extract the two-axis model, frame modes, and layout principles.
 */
function prepareCodeyArch() {
  const docPath = path.join(__dirname, '..', config.codeyArch?.docPath || '../codey-arch.md');

  if (!fs.existsSync(docPath)) {
    return null;
  }

  const content = fs.readFileSync(docPath, 'utf8');

  // Extract the two-axis model section
  const axisMatch = content.match(/### Two-axis model.*?\n\n([\s\S]*?)(?=\n### Speculative|$)/);
  const axisText = axisMatch?.[1] || '';

  // Extract frame modes (frame, bleed, spread, inset, rail/split)
  const frameModes = [
    {
      name: 'frame',
      title: 'Frame (Centered)',
      desc: 'Current 80% case refactored; symmetric gutter owned by grid (not body padding), centered max-measure track.',
      example: 'Default framed layout with centered content and symmetric margins.'
    },
    {
      name: 'bleed',
      title: 'Bleed (Edge-to-edge)',
      desc: 'Full-screen edge-to-edge; --gutter: 0, content supplies local padding.',
      example: 'Full-width backgrounds, hero sections, break-out media.'
    },
    {
      name: 'spread',
      title: 'Spread (Hybrid)',
      desc: 'Hybrid; framed by default with per-block .bleed opt-outs. Resolves the mode-switch problem on a single page.',
      example: 'Most flexible: frame by default, opt individual blocks into bleed.'
    },
    {
      name: 'inset',
      title: 'Inset (Content-defines)',
      desc: 'Content-defines-margin; gutter derived from content (asymmetric rail / sidebar / image sets the column).',
      example: 'Asymmetric layouts with fixed side panels or weighted columns.'
    },
    {
      name: 'rail',
      title: 'Rail / Split (Multi-region)',
      desc: 'Multi-region asymmetric shells (fixed side + fluid main). Enables two-panel layouts.',
      example: 'Account dashboard, docs with sidebar, split-view interfaces.'
    }
  ];

  // Extract skeleton axis info
  const skeletonMatch = content.match(/### header \/ main \/ footer.*?\n([\s\S]*?)(?=\n###|$)/);
  const skeletonText = skeletonMatch?.[1] || '';

  return {
    hasArch: true,
    axisDescription: axisText.trim(),
    skeletonDescription: skeletonText.trim(),
    frameModes,
    twoAxisSummary: 'Vertical (header/main/footer rows) and horizontal (full/content tracks) axes compose independently — they don\'t compete.',
    gridExplanation: 'Named CSS Grid tracks (full-start, content-start, content-end, full-end) with subgrid propagation for alignment inheritance.'
  };
}

// ─── Main ─────────────────────────────────────────────────────────────────────

/**
 * Type ladder — express the fluid scale as a range of named HTML-element steps
 * (extra-small … p … h1 … XXX-big) instead of echoing raw --text-* tokens.
 * Each label maps to one step of the extracted scale; the clamp() value drives a
 * live sample so you see the real fluid size.
 */
const TYPE_LADDER = [
  ['extra-small', 'text-xs'],
  ['small',       'text-sm'],
  ['p',           'text-base'],
  ['h5',          'text-lg'],
  ['h4',          'text-xl'],
  ['h3',          'text-2xl'],
  ['h2',          'text-3xl'],
  ['h1',          'text-4xl'],
  ['extra-big',   'text-5xl'],
  ['super-big',   'text-6xl'],
  ['XXX-big',     'text-7xl'],
];

/** Pull the min/max bounds out of a clamp(MIN, PREF, MAX) value for display. */
function clampBounds(value) {
  const m = String(value).match(/^clamp\(\s*([^,]+?)\s*,.*,\s*([^)]+?)\s*\)\s*$/);
  return m ? { min: m[1].trim(), max: m[2].trim() } : { min: value, max: value };
}

function prepareTypeScale(fonts) {
  const sizes = fonts?.size || {};
  return TYPE_LADDER.map(([label, token]) => {
    const raw   = sizes[token];
    const value = Array.isArray(raw) ? raw[0] : raw;
    if (!value) return null;
    const { min, max } = clampBounds(value);
    return { label, token, value, min, max };
  }).filter(Boolean);
}

function run() {
  console.log('\n📄 Building style guide...');

  const tokens = readTokens();

  const colorThemes = prepareColorThemes(tokens.colorThemes);
  const codeyArch = prepareCodeyArch();

  const view = {
    project:     tokens.project,
    colorThemes,
    colorCount:  tokens.colorCount || colorThemes.reduce((n, t) => n + t.count, 0),
    fonts:       prepareFonts(tokens.fonts),
    typeScale:   prepareTypeScale(tokens.fonts),
    spacing:     prepareSpacing(tokens.spacing),
    components:  prepareComponents(tokens.components),
    breakpoints: prepareBreakpoints(tokens.breakpoints),
    layouts:     prepareLayouts(tokens.layouts),
    codeyArch,
    hasColors:       colorThemes.length > 0,
    hasComponents:   Object.keys(tokens.components || {}).length > 0,
    hasBreakpoints:  Object.keys(tokens.breakpoints || {}).length > 0,
    hasLayouts:      (tokens.layouts?.patterns || []).length > 0,
    hasCodeyArch:    codeyArch?.hasArch === true,
    sources:         tokens._meta,
  };

  const template = fs.readFileSync(path.join(TMPL_DIR, 'guide.html'), 'utf8');
  const html     = mustache.render(template, view);

  fs.mkdirSync(OUT_DIR, { recursive: true });
  const outFile = path.join(OUT_DIR, 'index.php');
  fs.writeFileSync(outFile, html);

  console.log(`  ✓ Style guide written to ${outFile}`);
  console.log(`    Colors:     ${view.colorCount} across ${view.colorThemes.length} themes`);
  console.log(`    Fonts:      ${view.fonts.families.length} families, ${view.fonts.sizes.length} sizes`);
  console.log(`    Spacing:    ${view.spacing.length}`);
  console.log(`    Breakpoints:${view.breakpoints.length}`);
  console.log(`    Layouts:    ${view.layouts.patternCount} patterns, ${view.layouts.themeCount} themes`);
}

run();
