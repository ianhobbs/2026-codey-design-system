# Architecture

## Why this shape

The source project (`codey-new-2025`) works but is too entangled to reuse: the
design system, the build pipeline, and one site's content all live in the same
tree. Copying it to start a new site drags all of that coupling along.

This repo separates the **system** from the **implementation**. The system is a
small set of layered, versioned packages. Sites consume them. That inversion —
sites depend on the system, the system depends on nothing site-specific — is the
whole point.

## The layers

```
┌─────────────────────────────────────────────┐
│ starterkit / real sites   (implementations)  │  ← consume everything below
├─────────────────────────────────────────────┤
│ kirby plugin  (snippets, blocks, blueprints) │  ← Composer
├─────────────────────────────────────────────┤
│ css primitives (grid, off-page, effects…)    │  ← npm, references tokens
├─────────────────────────────────────────────┤
│ assets (fonts, icons)      tokens (props)    │  ← npm, foundation
└─────────────────────────────────────────────┘
```

Dependencies point downward only. A layer never reaches up into a consumer, and
never sideways into another site.

## Front-end vs. Kirby split

Kirby projects are half PHP, half front-end. The system honours that with two
distribution channels that version independently:

- **Composer** carries the PHP/Kirby layer (the plugin).
- **npm** carries the front-end layer (tokens, CSS, assets).

A site can bump its CSS without touching its Kirby plugin version and vice
versa.

## The invariant that prevents re-complexification

Each item that enters a package must pass two tests:

1. **No hardcoded value a token could express.** Colours, spacing, and type
   sizes come from `@codey/tokens`. This is what makes a primitive portable
   across brands — override the tokens, keep the primitive.
2. **No site-specific content.** No client logos, no licenses/keys, no
   assumptions about one site's page model. If it only makes sense for one
   project, it stays in that project.

Anything failing either test is *project*, not *system*, and does not belong
here.

## Source of primitives

The reusable CSS architecture (grid/container logic, off-screen transforms,
off-page / menu-stack / push-stack overlay families, subgrid alignment wiring)
is documented in the source project's `codey-arch.md`. Those notes are the map
of what qualifies as a primitive; the roadmap turns them into package contents.
