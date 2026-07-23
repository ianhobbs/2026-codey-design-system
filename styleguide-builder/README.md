# styleguide-builder

Extract design tokens and components from Stylus + Tailwind PHP projects
and generate a self-contained static style guide at `/build/styleguide/`.

---

## Setup

```bash
# From your project root, add this folder

cd your-php-project/

# Install dependencies

cd styleguide-builder/

npm install
```

---

## Configuration

Edit `styleguide.config.js` to point at your project's paths:

```js
paths: {
  stylusVars:     '../assets/css/_variables.styl',
  stylusAll:      '../assets/css/**/*.styl',
  tailwindConfig: '../tailwind.config.js',
  templates:      '../templates/**/*.php',
  partials:       '../templates/partials/**/*.php',
  compiledCss:    '../public/assets/css/main.css',  // fallback
  output:         '../public/styleguide',
}
```

---

## Workflow

### Step 1 — Before BEM conversion (existing projects)

Run the class audit to see what you're working with:

```bash
npm run audit:classes
```

Outputs `extractor/tokens/class-audit.txt` — a sorted inventory of all class
names found across PHP templates, classified into BEM/Tailwind/other.
Use this to plan your BEM mapping before converting templates.

---

### Step 2 — Convert templates to BEM

Update your PHP templates and Stylus files to use BEM naming:

```css
/* Before */
.card-header { }
.btn-primary { }

/* After (Stylus) */
.card
  &__header { }
.btn
  &--primary { }
```

Use `@apply` to compose Tailwind utilities into named BEM components:

```stylus
.btn
  @apply inline-flex items-center rounded-md font-medium transition

  &--primary
    @apply bg-blue-600 text-white hover:bg-blue-700

  &--secondary
    @apply bg-white text-gray-800 border border-gray-300
```

Optionally add `data-component` attributes to PHP templates for
components that aren't yet formalised in CSS:

```php
<div data-component="card" class="flex flex-col rounded-lg p-4">
  <h2 data-part="title">...</h2>
  <div data-part="body">...</div>
</div>
```

---

### Step 3 — Build the style guide

```bash
# Extract + merge + build in one command
npm run build
```

Or run steps individually:

```bash
npm run extract:stylus     # reads _variables.styl
npm run extract:tailwind   # reads tailwind.config.js
npm run extract:components # scans PHP templates
npm run merge              # combines into merged.json
node builder/build-guide.js # outputs HTML
```

---

### Watch mode (during development)

```bash
npm run watch
```

Rebuilds on any change to `.styl`, `.css`, `.php`, or `.js` files.

---

## Output

The style guide is written to `build/styleguide/index.php`.
It includes:

- **Colours** — hex swatches with token names and values
- **Typography** — font families (rendered live) and size scale
- **Spacing** — token table with visual bars
- **Components** — BEM blocks with elements, modifiers, source file references
- **Breakpoints** — responsive screen tokens

---

## Project structure

```
styleguide-builder/
├── styleguide.config.js         ← edit this for each project
├── package.json
├── extractor/
│   ├── extract-stylus.js        ← reads _variables.styl
│   ├── extract-tailwind.js      ← reads tailwind.config.js
│   ├── extract-components.js    ← scans PHP templates for BEM
│   ├── audit-classes.js         ← pre-BEM class inventory tool
│   ├── merge-tokens.js          ← combines all token sources
│   └── tokens/                  ← generated JSON (gitignore)
│       ├── stylus.json
│       ├── tailwind.json
│       ├── components.json
│       ├── merged.json
│       └── class-audit.txt
└── builder/
    ├── build-guide.js           ← generates HTML from merged tokens
    └── templates/
        └── guide.html           ← Mustache template for the guide
```

---

## Gitignore

Add to your project `.gitignore`:

```
styleguide-builder/extractor/tokens/*.json
styleguide-builder/extractor/tokens/*.txt
styleguide-builder/node_modules/
build/styleguide/
```

---

## Tips

- **Stylus variables** — keep all project tokens in `_variables.styl`.
  The extractor reads this file first so named tokens take precedence.

- **Tailwind** — set `tailwindCustomOnly: true` in config to show only
  your project's overrides, not the full Tailwind default palette.

- **Component discovery** — the `forceInclude` list in config ensures
  your core BEM blocks appear in the guide even if they have 0 usages
  in templates yet (useful early in a build).

- **Compiled CSS fallback** — if there's no Stylus source (e.g. a legacy
  project with flat CSS), the extractor falls back to parsing compiled CSS.
  Tokens will be less well-named but colours/fonts are still captured.
