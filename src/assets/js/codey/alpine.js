// ─────────────────────────────────────────────────────────────────────
//  Alpine — CSP build (core).
//
//  @alpinejs/csp evaluates no strings, so it runs under a strict
//  Content-Security-Policy without 'unsafe-eval'. That is the whole reason
//  it is the default here: akibeo/kirby-csp ships a nonce-based policy, and
//  the standard Alpine build would need 'unsafe-eval' to survive it.
//
//  The trade-off is real and it changes how you write markup. Directives take
//  component / property / method NAMES only — never inline expressions:
//
//      WRONG   x-data="{ open: false }"      @click="open = !open"
//      RIGHT   x-data="disclosure"           @click="toggle"
//
//  One documented exception: x-transition's class form. The CSP build only
//  calls evaluate() when the attribute value is a FUNCTION, so a literal class
//  string passes through untouched to registerTransitionsFromClassString.
//  x-transition:enter-start="nav-drawer-out" is therefore safe, and is what
//  lets the mobile drawer slide rather than only fade.
//
//  Register the component here, reference it by name in the template.
// ─────────────────────────────────────────────────────────────────────
import Alpine from '@alpinejs/csp'

// One disclosure primitive, reused. Declared on <body> in codey/frame, which
// is the nearest common ancestor of the toggle (inside <header>, from
// codey/header) and the drawer (a sibling of <header>, from codey/mobile-nav).
// They must share one scope, so <body> is not laziness — it is the only
// element that contains both.
//
// `expanded` returns a STRING so x-bind always writes the attribute rather
// than removing it on a falsy value: aria-expanded="false" is meaningful, a
// missing aria-expanded is not.
Alpine.data('disclosure', () => ({
  open: false,
  toggle() {
    this.open = !this.open
  },
  close() {
    this.open = false
  },
  get expanded() {
    return this.open ? 'true' : 'false'
  },
}))

window.Alpine = Alpine
Alpine.start()
