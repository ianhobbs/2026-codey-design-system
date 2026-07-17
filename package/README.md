# package/ — the canonical Codey design system

This is the single source of truth you version and `composer require`. Its
contents are synced into a consuming project's `src/` by
[`scripts/codey-sync.js`](scripts/codey-sync.js), scoped strictly to the zones
declared in [`codey-sync.json`](codey-sync.json).

```
package/
├── composer.json      name: ianhobbsmedia/codey-design-system (type: library → vendor/)
├── VERSION            semver, echoed into the project's src/.codey-version on sync
├── codey-sync.json    the sync manifest (source→dest zone map = clobber-safety contract)
├── scripts/
│   └── codey-sync.js  the copy script (run by Composer post-install / npm postinstall)
├── css/               → src/assets/css/codey/   (index.css, theme.css tokens, lib/, templates/)
├── kirby/             → src/site/plugins/codey/  (index.php + snippets/blocks/blueprints)
└── fonts/             → src/assets/fonts/codey/  (licensed core fonts)
```

**Authoring model:** refine the theme *here*, tag a release, then
`composer update` in each project pulls it and re-syncs. You never hand-edit the
synced copy inside a project — those folders are restored on every sync.

See [../docs/DESIGN-SYSTEM.md](../docs/DESIGN-SYSTEM.md) for the full mechanism,
the override contract, and the zone boundary.
