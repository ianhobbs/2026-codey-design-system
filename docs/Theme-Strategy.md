# Theme Strategy

> **DECISION (2026-07): pure Git delivery, baukasten model.** After this survey,
> Codey was migrated off the Composer-plugin/sync design to a **Git starter you
> clone** to begin each project — the theme runs in place, compiled by the
> project's own `src/ → build/` pipeline (Composer pulls Kirby into `build/`). The
> PHP layer stays as plain `src/site` files with the layout engine namespaced under
> `codey/` (plugin-promotion kept as a documented future path). CSS keeps a
> `codey/` core vs `_brand.css` project split. The survey below is the reasoning
> that led here.

How to structure and deliver the Codey design system, examined against how
other developers actually build and ship themes.

## The question this document answers

Codey currently tries to be a **Composer dependency** that injects itself into a
consuming project. Version 3.0.0 exposed the strain: one package now ships two
payloads with different lifecycles — CSS that must land where a build tool can
*compile* it, and PHP that must land where Kirby can *execute* it. In a flat
project those are the same place. In a `src` → `build` project (CodeKit) they are
two different roots, and no single Composer install path satisfies both.

Before committing to a fix, we survey how themes are built in the wild. Most of
the reference examples are **not** Composer-delivered — that itself is the first
finding worth sitting with.

### The one fixed constant

Whatever structure we land on **must support a Tailwind CSS build**. Tailwind v4
scans source files for class usage and compiles a stylesheet; any delivery model
that can't accommodate that step is disqualified before we start. Every option
below is judged against this.

### The two axes every example is scored on

1. **Delivery** — how does the theme reach a project? Git clone / template, or a
   package manager (Composer / npm)?
2. **Build vs source layout** — where does authored source live, where do
   compiled assets go, and is PHP treated as *source that compiles* or *code that
   runs in place*?

---

## Example 1 — Baukasten (`tobimori/kirby-baukasten`)

A mature, opinionated Kirby starterkit. The reference for "modern Kirby + Tailwind
v4 + Vite" done by someone who ships Kirby plugins for a living.

### Tooling setup — reduced

| Concern | Choice |
| --- | --- |
| **Delivery** | Git clone / "use this template". **Not** a Composer package — its own `composer.json` is `type: project`. |
| **What Composer delivers** | The *dependencies* (Kirby CMS + ~15 plugins), never the theme itself. |
| **Build tool** | **Vite** (`laravel-vite-plugin`), not CodeKit. |
| **CSS** | Tailwind v4 via `@tailwindcss/vite`; entry `src/styles/index.css`. Minified by Lightning CSS. |
| **JS** | TypeScript + Alpine, entry `src/index.ts`, bundled by Vite. |
| **Source root** | `src/` — `styles/`, `components/`, `utils/`, `index.ts`. Frontend assets only. |
| **Output root** | `public/dist/` (git-ignored), wired back to templates via Vite `manifest.json`. |
| **Kirby PHP** | `site/` — blueprints, snippets, templates, models. Committed and edited **in place**; never compiled, never mirrored. |
| **Webroot** | `public/` — custom Kirby roots in `public/index.php` put `base` one level up, so `site/`, `vendor/`, `data/` sit *above* the webroot. |
| **Local plugin** | `site/plugins/extended/` — the theme's own PHP extensions (helpers, hooks), built separately with `kirbyup`, autoloaded via `composer.json` → `autoload.files`. |
| **Third-party plugins** | git-ignored (`/site/plugins/*`) except `extended`; restored by Composer on install. |
| **Dev runner** | `mprocs` runs Kirby's PHP server, Vite, and the plugin watcher concurrently. |

### The structural idea that matters

Baukasten **has no src → build mirror**, and that is the whole point. It draws the
line in a different place than CodeKit does:

```
src/     → compiled → public/dist/     (assets that transform: TS, CSS)
site/    → runs in place                (PHP that executes: never built)
public/  → webroot                      (only compiled output + entry are here)
vendor/  → Composer                     (dependencies, above webroot)
```

CodeKit's convention — the one Codey/rosie inherit — mirrors *all* of `src/` to
`build/`, PHP included. Baukasten treats that as a category error: **PHP is not an
asset that compiles**, so it doesn't live in the pipeline at all. Only things that
genuinely transform (TypeScript, Tailwind CSS) have a source and an output; the
PHP is just *there*, in `site/`, owned and edited directly.

### Why this dodges Codey's two-payload problem entirely

Because the theme **is the project**, there is exactly one root, so "where does the
CSS go" and "where does the PHP go" never compete — the CSS compiles into
`public/dist/`, the PHP already lives in `site/`, done. There is no injection step,
no sync script, no second manifest. Composer's only job is to fetch dependencies.

That is the trade the next examples will let us price: baukasten buys a clean,
single-root build by **giving up reusability across projects**. You don't
`require` baukasten into five sites — you clone it once per site and own the copy.
Codey wants the opposite (one system, many sites, centrally updated), which is
exactly why it reached for Composer and inherited the two-payload strain.

### Pros / cons for Codey

**Pros**

- One root, so the Tailwind build is trivial and the constant is satisfied by
  construction.
- PHP-runs-in-place removes the entire class of "plugin landed where Kirby can't
  see it" bugs that 3.0.0 introduced.
- `public/`-as-webroot is a real security win (secrets, content, vendor all above
  the served directory) and is orthogonal to the delivery question — worth
  stealing regardless of what we decide.
- Vite + `manifest.json` is a first-class, well-supported Tailwind v4 path.

**Cons**

- Clone-and-own means **no central updates**. A fix to the system doesn't
  propagate to sites that cloned it; each is a fork from day one.
- That is a direct contradiction of Codey's stated goal (a versioned system many
  projects consume). Adopting baukasten's model wholesale means abandoning
  "dependency" for "template".
- Heavier toolchain (Vite, mprocs, kirbyup, TypeScript, Lightning CSS) than the
  CodeKit setup rosie uses today.

### The provocation to carry forward

Baukasten answers our hardest question by **refusing the premise**: it never
delivers a theme as a dependency, so it never has two payloads to place. The open
question for Codey becomes sharp — is it a *dependency a project consumes*, or a
*template a project forks*? The two-manifest mess in rosie is what happens when a
thing tries to be both.

---

## Example 2 — Zero One (`thezero.club`, commercial)

A paid Kirby theme sold as a licensed zip. Chosen as the deliberate *opposite
extreme* to baukasten: where baukasten hands a developer a modern build
toolchain, Zero One hands a buyer a finished site with **no build step at all**.

### Tooling setup — reduced

| Concern | Choice |
| --- | --- |
| **Delivery** | Commercial **zip download** (license PDF, "How to install.txt"). Not Git, not a package manager. |
| **What ships in the zip** | *Everything.* Kirby core (`kirby/`), all ~14 plugins, content, demos — a whole runnable site. |
| **Install** | "Copy the `zero-one` folder to your server. It works straight away." No `composer install`, no `npm install`. |
| **Build tool** | **None on the developer's machine.** |
| **CSS framework** | **UIkit**, not Tailwind. |
| **CSS build** | A **runtime LESS compiler** (`lessphp`, a bundled PHP plugin). The editor toggles "Style Compiler" *in the Panel*; CSS is generated on the frontend from LESS + Panel-set variables, on first request. |
| **Source vs output** | `assets/app/custom/**/*.less` (source) → compiled to `assets/app/dist/css/*` (output), **by PHP at runtime**, not by a dev tool. |
| **Kirby PHP** | `site/theme/` — custom roots in `index.php` point `blueprints`, `controllers`, `models`, `snippets`, `templates` into a `theme/` subfolder, isolating theme code from the buyer's `site/config`. |
| **Demos** | Five swappable `content/` folders (architecture, consulting, furniture, personal, blank). Theming is content + Panel settings, not code. |
| **Updating** | Manual — download a new zip, follow per-release migration notes. No dependency resolution. |

### The structural idea that matters

Zero One moves the **entire build into the CMS at runtime**. There is no
developer toolchain because the compile step is a feature of the running site: an
editor flips a Panel switch and `lessphp` regenerates the stylesheet from
Panel-configured variables. The "source" (LESS) and "output" (dist CSS) both live
inside the shipped product; the transform happens on the server on demand.

This is coherent for its audience — a **buyer who is not a developer** and never
opens a terminal. It is also the reason the theme can bundle Kirby itself and
call install "copy the folder": there is nothing to resolve or build, so there is
nothing that can go wrong at install time.

### Pros / cons for Codey

**Pros**

- Zero install friction — no toolchain, no Node, no Composer step. Instructive for
  the *editor* experience even if not the developer one.
- Panel-configurable theming (colours, fonts as Panel fields feeding the
  compiler) is a genuinely nice pattern Codey could borrow for brand tokens —
  imagine `_brand-palette` values editable in the Panel rather than in a CSS file.
- Theme-code isolation via `site/theme/` custom roots is a clean way to separate
  "the system" from "the project's own `site/`" — conceptually close to what
  Codey's plugin registration achieves, but done with roots instead.

**Cons — and one is disqualifying**

- **Fails the fixed constant outright.** It's UIkit + a runtime LESS compiler.
  There is no Tailwind build, and the runtime-compile model is fundamentally
  incompatible with Tailwind v4's file-scanning compile step. This option is off
  the table for Codey as-is; it earns its place only as a source of ideas.
- Bundling Kirby + vendored plugins means **no dependency management** — security
  updates are manual zip swaps. The exact opposite of Composer's value.
- Runtime compilation is a performance and caching liability, and puts a LESS
  toolchain on the *production server* rather than the developer's machine.
- Zip delivery means no version control of the boundary between theme and project;
  a buyer's edits and a theme update collide by hand.

### The provocation to carry forward

Zero One shows the far end of "delivery simplicity": push everything, including
the build, into the shipped artifact, so the consumer does nothing. Codey can't
follow it on the build (Tailwind forbids it), but the **Panel-configurable brand
layer** is worth stealing — it answers "how does a non-developer rebrand the
system" better than editing `_brand.css` does. Hold that thought against
baukasten's opposite bet, that the consumer *is* a developer with a full
toolchain.

---

## Example 3 — Firma (`clicktonext.com`, commercial)

Another paid theme, same UIkit family as Zero One — but structurally it is the
**closest analog to what Codey is trying to be**, and the most instructive example
in the survey. Firma is the first example that delivers its theme *as a Kirby
plugin*.

### The two-level structure

Firma ships as a zip, but inside it there are two distinct layers:

1. **The outer project** (`composer.json` → `type: project`) is a bare Kirby 3
   install. Its `index.php` is the stock two-liner; `site/templates` and
   `site/snippets` are **empty**. The project holds almost nothing of its own.
2. **The theme itself** is a single Kirby plugin at
   `site/plugins/clicktonext` (`composer.json` → `type: kirby-plugin`). Everything
   the theme *is* — templates, snippets, blocks, components, field extensions, the
   CSS compiler — lives inside that one plugin and is **registered**, not copied
   into `site/`.

### How the registration works — a nested plugin loader

The plugin's `index.php` does something Codey should look at closely:

```php
Kirby::plugin('clicktonext/loader');
(new Loader())->register();
```

`Loader::register()` walks three sub-directories — `plugins/`, `extensions/`,
`blocks/` — and for each child folder calls `F::loadOnce($child/index.php)`. In
other words, **one plugin bootstraps dozens of self-contained sub-plugins**. Each
block (`component-accordion`, `block-navbar`, …) and each extension
(`kirby-theme-core`, `kirby-theme-options`, `kirby-uikit`,
`kirby-less-compiler`, …) is itself a normal Kirby plugin with its own
`index.php`. The loader is a mini plugin-manager that turns a directory tree into
registrations at boot. Loading order is explicit and commented as significant.

This is the pattern Codey is reaching for — register theme code from a plugin
instead of syncing it into the consumer's `site/` — proven out at *full theme
scale*: not just a few snippets, but blocks, panel fields, and options screens,
all registered from one package.

### CSS — same runtime-compile trick as Zero One

CSS is UIkit again. Source (`assets/app/theme/**/*.less` + some `.scss`) is
compiled by a bundled **`kirby-less-compiler`** extension — the same
`wikimedia/less.php` PHP engine Zero One uses — at runtime, inside the CMS. So
Firma sidesteps the consumer-build problem the same way Zero One does: there is no
consumer build step, because the compiler *is a plugin that runs on the server*.

### Why this example matters most for Codey

Firma answers the exact question 3.0.0 raised, and answers it in two parts:

- **The PHP half — solved, and validated.** Firma proves the
  register-from-a-plugin model scales to an entire theme. Codey's instinct
  (registration over sync) is not exotic; a shipping commercial theme does exactly
  this, via a loader worth copying almost verbatim. The nested-loader idea also
  means Codey could group its own concerns (blocks, snippets, fields) as
  sub-registrations rather than one flat list.
- **The CSS half — solved *by cheating*, in a way Codey can't.** Firma has no
  two-payload problem because it has **no consumer CSS build at all** — it moved
  compilation to a runtime PHP plugin. That's only possible because it's LESS +
  UIkit. Codey keeps Tailwind v4, whose compile step must run at build time
  against scanned source. So Codey cannot make its CSS payload disappear the way
  Firma does; the CSS still has to land somewhere a Tailwind build can see it.

That split is the sharpest framing the survey has produced: **Codey's PHP problem
has a proven solution (Firma's loader); Codey's CSS problem is the genuinely hard
part, and it's hard precisely because Codey — unlike every commercial example —
insists on a real build-time CSS toolchain.**

### Pros / cons for Codey

**Pros**

- Direct proof the plugin-registration delivery model works for a whole theme,
  including blocks and Panel fields.
- The `Loader` is a clean, reusable pattern: a directory of self-contained
  sub-plugins, registered in a controlled order. Better than one monolithic
  `index.php`.
- Keeps the consumer's `site/` almost empty — the theme/project boundary is crisp
  and survives updates (replace the plugin, keep your content and config).

**Cons**

- CSS strategy is non-transferable: UIkit + runtime LESS fails the Tailwind
  constant, and the runtime compiler is a production-server dependency.
- Commercial zip delivery with bundled Kirby → no real dependency management, same
  as Zero One.
- The nested-loader cleverness has a cost: registration order matters and is
  managed by hand; debugging a mis-registered sub-plugin is harder than reading a
  single flat `index.php`.

### The provocation to carry forward

Firma is the proof of concept for Codey's PHP direction and the clearest statement
of what's left: adopt something like the `Loader` for the executable half, and
accept that the CSS half is Codey's *own* problem to solve, created by the (good)
decision to keep a real Tailwind build that none of the commercial themes attempt.

---

## Example 4 — Index (`eddiedale/index-theme`, MIT, open source)

The simplest theme in the survey, and the one that most directly answers Codey's
question — because it is the only reference example built for **Kirby 5 + PHP 8.2**
(Codey's exact target) and delivered the way Codey wants to be delivered: as a
`type: kirby-plugin` installed into `site/plugins/`.

### Delivery — a plugin, three ways into the same slot

`composer.json` is `type: kirby-plugin` with `getkirby/composer-installer`, and the
README documents three install tracks that all land the theme at
`site/plugins/index-theme/`:

- **Composer** — `composer require eddiedale/index-theme` onto a Plainkit base.
- **ZIP** — download a release, drop the folder into `site/plugins/`.
- **Git submodule** — `git submodule add … site/plugins/index-theme`, updated with
  `git submodule update --remote`.

One destination, three delivery mechanisms. This is the first example that treats
"be a plugin" and "be installable by Composer" as the *same* decision rather than
competing ones — which is exactly the framing Codey needs.

### PHP — everything registered, nothing synced

The whole theme is one `Kirby::plugin('eddiedale/index-theme', [...])` call
registering `blueprints`, `snippets`, `templates`, `collections`, `pageModels`,
`hooks`, and `routes` (RSS `feed.xml`, `sitemap.xml`). No file is copied into the
consumer's `site/`. This is Codey's PHP model in its purest form — and, like
Firma, it confirms the model is sound. Where Firma used a bespoke nested `Loader`,
Index just uses Kirby's plain plugin-registration array. For Codey's scale, the
plain array is almost certainly the right amount of machinery.

Override story is idiomatic Kirby: the consumer customises by dropping a
same-named file into `site/templates/` or `site/snippets/` (Kirby prefers `site/`
over plugin files), plus a dedicated `header-extra.php` seam for injecting
`<head>` content without touching the theme. No sync, no eject — just Kirby's
native override precedence.

### CSS — the answer the other examples dodged

This is why Index matters most. It has **no build tools** — "no npm, no
preprocessors, no frameworks" — and ships a hand-written static
`assets/css/style.css` *inside the plugin*. Kirby serves it straight from the
plugin's asset URL. Customisation is two CSS variables (primary + accent) and a
font swap.

Recall the bind from the other three examples:

- Baukasten keeps a real CSS build (Vite/Tailwind) but only by **being the
  project**.
- Zero One and Firma are plugins/folders with **no consumer build**, but only by
  compiling LESS **at runtime** on the server.

Index shows the fourth corner: **a plugin that ships pre-written CSS as a static
asset, with no build anywhere.** The browser gets exactly the bytes the author
committed. That is trivially possible for Index because its CSS is hand-authored
plain CSS — there is nothing to compile.

### The move this unlocks for Codey

Index can't be copied on CSS directly — Codey's stylesheet is Tailwind *source*
that must be compiled, not hand-written plain CSS. But Index reveals the resolution
3.0.0 missed by generalising its structure:

> **Compile Codey's Tailwind at the theme's *own* release time, then ship the
> compiled `main.css` as a static plugin asset — exactly the way Index ships
> `style.css`.**

3.0.0's two-payload pain came from assuming the *consumer* compiles the CSS (so the
CSS had to land where the consumer's build tool could see it — the root that
Composer couldn't satisfy). Index points at the alternative: Codey's *own* repo
already has the Tailwind/CodeKit toolchain, so run the build there, at authoring
time, and ship only the output. Then there is genuinely **one payload — a plugin**
— the PHP is registered and the CSS is a static asset beside it. The consumer runs
no build at all. That achieves Firma/Zero One's "no consumer build" outcome *while
keeping Tailwind*, by moving the compile to release time instead of runtime.

### The real trade-off to decide

Shipping pre-compiled CSS freezes the utility set at release. If Codey consumers
are expected to write **new** Tailwind classes in their own templates and have them
compiled, a frozen stylesheet won't see that markup — they'd get only the classes
Codey's build already emitted. So the decision Index forces into the open is:

- **Codey as a finished stylesheet** (pre-compiled asset): consumers theme via CSS
  variables and overrides, like Index's two-colour palette. Zero consumer build.
  Simplest, and matches how three of four examples actually behave.
- **Codey as a Tailwind *source layer*** the consumer compiles: consumers can
  extend utilities against their own markup, but must run a Tailwind build that is
  configured to `@source`-scan the plugin folder — reintroducing exactly the
  build-coupling 3.0.0 struggled with.

Index is a working argument for the first option.

### Pros / cons for Codey

**Pros**

- Exact-match stack (Kirby 5, PHP 8.2, `type: kirby-plugin`) — the only reference
  example that is.
- Cleanest possible demonstration that Composer-install and plugin-registration are
  one decision, not two.
- Shows a real, shippable answer to "where does the CSS go" that keeps a single
  payload: **static compiled asset in the plugin**.
- Native Kirby override precedence for customisation — no sync, no eject, no
  manifest.

**Cons**

- Its CSS is trivial (plain, hand-written); it never has to solve Tailwind, so it
  models the *delivery* of compiled CSS, not the *building* of it. Codey still owns
  the build — it just moves to release time.
- Minimalist scope (no blocks, no complex nav) means it doesn't stress-test
  registration the way Firma does. Truth is between them: Index's plain array,
  Firma's proof it scales.

### The provocation to carry forward

Index closes the survey by showing the target shape concretely: **a Kirby-5
`kirby-plugin` that registers its PHP and ships its CSS as a static asset, Composer-
or ZIP- or submodule-installable into `site/plugins/`, with no consumer build
step.** For Codey the single open question is no longer *how to deliver* — it's
whether to ship CSS **pre-compiled** (Codey builds Tailwind at release) or **as
source** (consumer builds Tailwind). Everything else in the survey now points the
same way.

---

## Scoring Codey itself

The field is surveyed; now Codey goes on the same two axes.

**Delivery.** Today Codey is a *Composer dependency that injects itself into a
consuming project* — the only example in the survey that tries to be a dependency
rather than a plugin or a project. The target, following Index, is a **Kirby
plugin installed into `site/plugins/codey`**, reachable by Composer *or* ZIP. That
is the same artifact either way; "Composer-installable" and "is a plugin" stop
being two decisions.

**Build / source layout.** Today Codey ships **two payloads with different
lifecycles**: CSS that must land where the consumer's CodeKit/Tailwind build can
compile it, and PHP that must land where Kirby can register it. In a flat project
those are one place; in a `src → build` project they are two, and no single
Composer path serves both. That split *is* the 3.0.0 pain. The target is **one
payload**: PHP registered by the plugin, CSS pre-compiled at Codey's own release
and shipped as a static asset beside it — no consumer build in the default path.

| Axis | Codey today | Codey target |
| --- | --- | --- |
| Delivery | Composer dependency injecting into consumer | Plugin in `site/plugins/codey` (Composer or ZIP) |
| PHP | Registered — but alongside a sync step | Registered only; consumer overrides via `site/` |
| CSS | Synced into consumer build root, consumer compiles | Pre-compiled at Codey's release, shipped as static asset |
| Payloads | Two (different lifecycles) | One (a plugin) |

## The decision Index forced: pre-compiled asset vs source layer

This is the one genuinely open choice. Both keep the plugin delivery; they differ
only in what the plugin puts in `assets/css`.

**Option A — ship a finished, pre-compiled stylesheet.** Codey runs Tailwind at
*its own* release and commits the compiled `main.css` into the plugin. The consumer
installs the plugin and links the stylesheet — **no build, no Node, no Tailwind
config on their side.** Theming is done through Codey's CSS-variable layer (the
Utopia ramps, brand colours) and Kirby's file-override precedence, exactly the way
Index themes through two variables.

- *Cost:* the utility set is **frozen at release**. If a consumer writes a
  brand-new Tailwind class in *their own* template, the shipped `main.css` was never
  compiled against that markup, so the class produces no CSS. This is also the
  world where `kirby-tailwind-merge` earns its keep (see above): with a frozen
  sheet, `merge()` is how snippets keep caller-wins override precedence.
- *Mitigation:* Codey's release build scans **all of Codey's own** templates,
  snippets, and blocks, so every utility Codey's components use is present. Consumers
  assembling pages from Codey's components, and theming via tokens, never hit the
  frozen edge.

**Option B — ship the Tailwind source as a layer the consumer compiles.** The
plugin ships its CSS source and a documented `@source` entry the consumer adds to
their Tailwind config, so the consumer's build scans Codey's files *and* their own.

- *Benefit:* consumers can author new utilities against their own markup and extend
  the system freely.
- *Cost:* reintroduces exactly the build-coupling 3.0.0 struggled with — the
  consumer must run Tailwind, configure `@source` to reach into `site/plugins/codey`,
  and keep versions aligned. It puts a payload back into the consumer's build root,
  which is the problem this whole exercise set out to remove.

### Recommendation

**Ship Option A as the default, and offer Option B as a documented opt-in — don't
force the choice on every consumer.**

Three of the four surveyed themes behave like Option A (finished CSS, theme via
variables), and it is the only option that actually delivers the "one payload, no
consumer build" outcome the survey converged on. Make it the paved road: the
plugin ships a pre-compiled `main.css`, built at release against all of Codey's own
components, and the consumer just links it. For the minority who need to author
their own Tailwind, document the Option B path (ship the source, publish the
`@source` snippet) as an advanced mode — the same repo can serve both, since the
source is present either way and only the build location differs.

This resolves 3.0.0 cleanly: the default path has **no** two-payload problem
because the consumer has no CSS build at all, and the escape hatch exists for
anyone who genuinely needs it, clearly labelled as the more involved route.

### The one question that is genuinely Ian's to answer

Everything above holds *unless* the primary way Codey will be used is consumers
writing large amounts of their **own** Tailwind utility markup (not assembling
Codey's components, not theming via tokens). If that is the expected usage, Option
B stops being an edge case and the default should flip. So the single input needed
before locking this in: **do you expect Codey's consumers to author their own
Tailwind classes, or to compose Codey's components and theme through tokens?** The
recommendation above assumes the latter — which is what the hybrid, token-driven
architecture in `CLAUDE.md` is built for.

## Running tally of tooling setups

| Theme | Delivery | Build tool | CSS system | PHP treated as |
| --- | --- | --- | --- | --- |
| Baukasten | Git clone / template | Vite (consumer) | Tailwind v4, `src/styles/index.css` | runs in place (`site/`) |
| Zero One | Commercial zip | none (runtime LESS in PHP) | UIkit + LESS — **fails Tailwind constant** | runs in place (`site/theme/`) |
| Firma | Commercial zip | none (runtime LESS in PHP) | UIkit + LESS — **fails Tailwind constant** | **registered from a plugin** (nested loader) |
| Index | Composer / ZIP / submodule → `site/plugins/` | **none** | plain static `style.css` in the plugin | **registered from a plugin** (plain array) |
| Codey (today) | Composer dependency | CodeKit (consumer) | Tailwind v4, consumer `main.css` | mixed: synced CSS + registered PHP |
| **Codey (target)** | **Composer / ZIP → `site/plugins/`** | **CodeKit at Codey's release time** | **pre-compiled `main.css` shipped as plugin asset** | **registered from a plugin** |

### What the survey showed — and where it lands

Four examples map the four corners of the design space:

- **Baukasten** — real build-time CSS (Tailwind/Vite), but pays for it by *being
  the project*.
- **Zero One** — a folder with no consumer build, by compiling LESS *at runtime*.
- **Firma** — a *plugin* with no consumer build, by compiling LESS *at runtime*;
  proves plugin-registration scales to a full theme.
- **Index** — a *plugin* with no build anywhere, by shipping *pre-written static
  CSS*; exact Kirby-5 / `kirby-plugin` match for Codey.

Two conclusions, now firm:

**The PHP half is settled.** Registration-from-a-plugin is the norm, not the
exception — Firma and Index both do it, and Kirby's own override precedence handles
customisation without any sync. Codey's synced-PHP mechanism from 3.0.0 is the
outlier and should go; the plugin registers its PHP and the consumer overrides via
`site/` when needed.

**The CSS half has a shape.** No example makes the *consumer* run a Tailwind build
— they either avoid Tailwind (runtime LESS) or avoid a consumer build (static CSS).
The synthesis for Codey, drawn from Index: **move the Tailwind build to Codey's own
release step and ship the compiled `main.css` as a static plugin asset.** That
collapses 3.0.0's two payloads into one plugin with no consumer build — Firma's
outcome, achieved while keeping Tailwind. The one remaining decision is whether
consumers ever need to compile *their own* Tailwind against the theme (source
layer) or can live with a frozen, variable-themeable stylesheet (compiled asset) —
and three of four examples behave like the latter.

---

## Bridge candidate — `tobimori/kirby-tailwind-merge`

Added to the survey as a proposed **CSS bridge**. The label needs a precise
reading, because the plugin does *not* address the same seam the four themes do —
and separating the two is what makes it useful rather than confusing.

### What it actually is

A Kirby 4/5 plugin (`type: kirby-plugin`, PHP 8.3+) by Tobias Möritz that wraps
`tales-from-a-dev/tailwind-merge-php`. It exposes three template helpers —
`merge()`, `cls()`, `attr()` — that **resolve conflicting Tailwind utility strings
at render time**. Give it `'h-full w-full bg-neutral-100'` plus an incoming
`'w-1/2'`, and it returns `'w-1/2 h-full bg-neutral-100'` — the later `w-1/2` wins
over `w-full` because tailwind-merge knows those two belong to the same conflict
group. It also supports conditional maps (`'py-32' => true`) and nesting via
`cls()`, and caches results through a Kirby cache adapter.

Crucially, it is a **runtime PHP string resolver**, not a build tool. It never
scans files, never compiles CSS, never emits a stylesheet. It knows Tailwind's
*conflict-group rules* and uses them to decide which of two utilities should
survive. It is entirely orthogonal to the compile-and-deliver question the survey
just settled.

### Which seam it actually bridges

The survey's CSS question was **build/delivery**: where the compiled `main.css`
comes from and who runs Tailwind. This plugin touches a *different* seam — the one
between **registered-plugin PHP** (the model the survey landed on) and the
**Tailwind utility classes** those snippets emit:

> When a theme is delivered as registered snippets, every reusable component wants
> to ship sensible default utilities *and* let the caller override them via a
> `$class` prop. Naive concatenation produces `w-full w-1/2` and lets raw source
> order in the compiled sheet decide the winner — which, with a frozen pre-compiled
> `main.css`, is not something the consumer can influence. `merge()` makes the
> *caller's* class win deterministically, at render time, regardless of stylesheet
> order.

So it is a bridge, but between **component authoring and Tailwind**, not between
Codey and the consumer's build. It is the composition glue that makes a
plugin-delivered, Tailwind-styled component set ergonomic — and it matters *most*
in exactly the world Index pointed Codey toward: a **pre-compiled, frozen
stylesheet**, where deterministic override precedence can no longer come from
editing source order and has to come from the class string itself.

### Why it fits Codey's hybrid specifically

Codey's CSS architecture is a deliberate hybrid — Tailwind utilities for
structural layout in templates, plus authoritative `@layer bespoke` components
(see `CLAUDE.md`). The friction point that hybrid creates is precisely
prop-driven overrides on snippets that carry default utilities. `merge()`/`cls()`
resolve that cleanly and would let Codey's registered snippets expose overridable
layout defaults without cascade-order guesswork. It slots into the settled PHP
model with zero conflict: it is just another registered plugin the theme can
depend on.

### The real costs — two of them, both worth stating

1. **A second place the token vocabulary must be mirrored.** tailwind-merge-php
   carries its own model of Tailwind's default groups and scales. Codey's `@theme`
   is heavily re-engineered — it *wipes* Tailwind's default colour and spacing
   scales and substitutes Utopia ramps, and may use a prefix. If Codey's custom
   groups aren't described to the resolver through its `config` option (array or
   closure), it will mis-merge — treat non-conflicting classes as conflicting or
   vice-versa. That is a direct tension with `CLAUDE.md`'s "one well" rule: the
   merge config becomes a second mirror of the token system that can silently
   drift from `@theme`.
2. **A consumer-side integration step.** To make `attr()` override Kirby's
   built-in, the consumer must add `define('KIRBY_HELPER_ATTR', false);` at the top
   of their `index.php`, before Kirby loads. That is the one thing here that is not
   "just install the plugin" — a small dent in the zero-consumer-config ideal Index
   modelled. (Using only `merge()`/`cls()` avoids it, since those are new function
   names.)

### Verdict for Codey

Adopt it, but file it correctly. It is **not** the answer to the build/delivery
question — that answer is still "pre-compile at Codey's release, ship a static
`main.css`." It *is* a strong answer to the **component-override** question that
the pre-compiled-stylesheet choice sharpens: with a frozen sheet, `merge()` is how
snippets keep deterministic, caller-wins class precedence. Treat it as an optional
authoring dependency of the theme, gate its value on keeping its `config` in sync
with Codey's `@theme`, and prefer `merge()`/`cls()` over the `attr()` override to
avoid the consumer `define()`. On the two survey axes it is a non-event (it is a
plugin, it ships no CSS); its whole contribution is at the template-authoring
layer.

### In plainer terms

That verdict is dense, so here it is unpacked — the mechanism first, because
everything rests on it.

**The problem it solves.** In CSS, when two classes fight — say `w-full` and
`w-1/2` — and both sit in the same HTML `class` attribute, the winner is decided by
**which rule sits later in the compiled `main.css`**, *not* by the order you typed
the classes in the HTML. That is simply how the cascade resolves a tie at equal
specificity. So if a snippet hard-codes `class="w-full"` and the caller passes
`class="w-1/2"`, both end up on the element and the winner is whatever Tailwind
happened to emit last in the stylesheet. The person writing the template has no
easy control over that.

`merge()` fixes it *before the HTML is rendered* by deleting the losing class from
the string, so only `w-1/2` ever reaches the element. No conflict is left for the
cascade to resolve, and the caller reliably wins. That is the entire plugin: it
edits class strings in PHP, using its built-in knowledge of which Tailwind
utilities belong to the same group.

**Now the verdict, claim by claim:**

1. *"Not the answer to build/delivery."* It does nothing about how Codey's CSS is
   compiled or shipped. That question is already answered (pre-compile at release,
   ship `main.css`). This is a separate concern that happens to also involve
   Tailwind.
2. *"The component-override question the pre-compiled choice sharpens."* Once Codey
   ships a **frozen** stylesheet, you can no longer fix a class conflict by
   re-ordering the compiled output — it is fixed at release. So resolving the
   conflict in the class string itself (what `merge()` does) becomes the *only*
   remaining lever. Freezing the CSS makes this plugin **more** useful, not less.
3. *"Keep its `config` synced to `@theme`."* The plugin carries its own copy of
   Tailwind's rules about which classes conflict. Codey rewrote those rules (it
   replaced Tailwind's default colour and spacing scales with Utopia ramps). If the
   plugin isn't told about those changes via its `config` option, it will guess
   wrong about which classes fight.
4. *"Prefer `merge()`/`cls()` over the `attr()` override."* The `attr()` helper only
   works if the consumer adds `define('KIRBY_HELPER_ATTR', false);` to their
   `index.php`. `merge()` and `cls()` are brand-new function names, so they need no
   such step. Using them keeps consumer setup at zero.
5. *"A non-event on the two survey axes."* The survey scores every example on
   *delivery* and *build/source layout*. This is just a normal plugin that ships no
   CSS, so it moves neither needle. Its only contribution is at the moment a snippet
   writes `class="..."`.

In one sentence: **it is not part of how Codey is delivered or built — it is a
convenience for the person writing Codey's snippets, and it earns its place only
because the frozen-stylesheet decision takes away the other way of controlling
which class wins.**
