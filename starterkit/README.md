# Codey Starterkit

The minimal Kirby site that consumes the design system. Copy this folder to
begin a new project, then install the packages.

## Setup

```bash
cp -R starterkit ../my-new-site && cd ../my-new-site

# Kirby layer
composer require getkirby/cms
composer require ianhobbsmedia/codey-design-system

# Front-end layer
npm install @codey/tokens @codey/css @codey/assets
```

Then wire the CSS + fonts into `site/snippets/head.php` (see the stub there) and
start building pages. Brand overrides go in your own tokens file, layered after
`@codey/tokens`.

## What belongs here vs. in the system

This starterkit holds only what is unique to a site: its content, its config
(domain, license, keys), and its brand token overrides. Everything reusable —
snippets, blocks, blueprints, CSS primitives — comes from the packages. If you
find yourself copying a reusable piece into here, it probably belongs back in a
package instead.
