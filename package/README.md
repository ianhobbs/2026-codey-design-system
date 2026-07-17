# package/ — the canonical Codey design system

This is the single source of truth you version and `composer require`. Its
contents are synced into a consuming project's `src/` by
[`scripts/codey-sync.cjs`](scripts/codey-sync.cjs), scoped strictly to the zones
declared in [`codey-sync.json`](codey-sync.json).

```
package/                (the root composer.json — name: ianhobbsmedia/codey-design-system — points its bin here)
├── VERSION            semver, echoed into the project's src/.codey-version on sync
├── codey-sync.json    the sync manifest (source→dest zone map = clobber-safety contract)
├── scripts/
│   └── codey-sync.cjs the copy script (run by Composer post-install / npm postinstall)
├── css/               → src/assets/css/codey/
│   ├── index.css      opinionated manifest (core on, optional components commented)
│   ├── theme.css      @theme Utopia type/space tokens
│   ├── globals.css    :root globals + @font-face
│   ├── palettes/      raw palette: _codey
│   ├── themes/        semantic colour map: theme-codey
│   ├── lib/           layout, typography, elements + form/accordion/transitions/cards (seeds)
│   └── templates/     core per-template defaults (e.g. note.css)
├── js/                → src/assets/js/codey/   shared JS (e.g. Alpine init)
├── kirby/             → src/site/plugins/codey/
│   ├── index.php      registers codey/* snippets + fields/codey-layout
│   ├── snippets/      layout (shell), header, footer, layouts (renderer), card
│   ├── blueprints/    fields/layout.yml
│   └── templates/     default.php (example, not registered)
└── fonts/             guidance only — fonts are a project override, NOT synced
```

**Authoring model:** refine the theme *here*, tag a release, then
`composer update` in each project pulls it and re-syncs. You never hand-edit the
synced copy inside a project — those folders are restored on every sync.

**Opinionated manifest:** `css/index.css` keeps optional components commented so
they ship zero bytes; a project uncomments only the markup it uses. The optional
`lib/{form,accordion,transitions,cards}.css` are token *seeds* with guidance
comments, not full components.

See [../docs/DESIGN-SYSTEM.md](../docs/DESIGN-SYSTEM.md) for the full mechanism and
[../docs/ROADMAP.md](../docs/ROADMAP.md) for extraction status.
