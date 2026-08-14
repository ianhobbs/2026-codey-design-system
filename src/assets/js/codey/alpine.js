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
//  Register the component here, reference it by name in the template.
// ─────────────────────────────────────────────────────────────────────
import Alpine from '@alpinejs/csp'

// Site nav — declared on <body> in codey/frame, toggled from codey/header.
Alpine.data('nav', () => ({
  showNav: false,
  toggle() {
    this.showNav = !this.showNav
  },
}))

window.Alpine = Alpine
Alpine.start()
