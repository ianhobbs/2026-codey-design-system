<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Codey.net.au — Style Guide</title>
  <style>
    /* ── Site typefaces — real previews. Absolute paths resolve when the
         guide is served at /styleguide/ (build/ is the Kirby webroot). ── */
    @font-face { font-family: "Gradual";         src: url(/assets/fonts/BwGradual-Regular.woff2) format("woff2"); font-display: swap; }
    @font-face { font-family: "Gotham-Med";      src: url(/assets/fonts/GothamHTF-Medium-export/GothamHTF-Medium.woff2) format("woff2"); font-display: swap; }
    @font-face { font-family: "Gotham-Ital";     src: url(/assets/fonts/GothamHTF-BookItalic-export/GothamHTF-BookItalic.woff2) format("woff2"); font-display: swap; }
    @font-face { font-family: "Gotham-Med-Cond"; src: url(/assets/fonts/GothamHTF-MediumCondensed-export/GothamHTF-MediumCondensed.woff2) format("woff2"); font-display: swap; }

    /* ── Reset & base ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --c-bg:        #0f0f11;
      --c-surface:   #1a1a1e;
      --c-border:    #2a2a30;
      --c-text:      #e8e8ec;
      --c-muted:     #888896;
      --c-accent:    #5b6ef5;
      --c-accent-lo: #5b6ef518;
      --c-ok:        #3ecf8e;
      --c-warn:      #f5a623;
      --c-info:      #5bc8f5;
      --font-mono:   'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
      --font-ui:     -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      --radius:      6px;
      --radius-lg:   12px;
      --sidebar-w:   220px;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-ui);
      background:  var(--c-bg);
      color:       var(--c-text);
      font-size:   15px;
      line-height: 1.6;
      min-height:  100vh;
    }

    /* ── Sidebar ──────────────────────────────────────────── */
    .sidebar {
      position:   fixed;
      top:        0;
      left:       0;
      width:      var(--sidebar-w);
      height:     100vh;
      background: var(--c-surface);
      border-right: 1px solid var(--c-border);
      overflow-y: auto;
      padding:    24px 0 40px;
      display:    flex;
      flex-direction: column;
      gap: 4px;
    }

    .sidebar__brand {
      padding:      0 20px 20px;
      border-bottom: 1px solid var(--c-border);
      margin-bottom: 12px;
    }

    .sidebar__project {
      font-size:   11px;
      color:       var(--c-muted);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-bottom: 4px;
    }

    .sidebar__name {
      font-size:   14px;
      font-weight: 600;
      color:       var(--c-text);
    }

    .sidebar__version {
      font-size:  11px;
      color:      var(--c-muted);
      margin-top: 2px;
    }

    .sidebar a {
      display:     flex;
      align-items: center;
      gap:         8px;
      padding:     7px 20px;
      color:       var(--c-muted);
      text-decoration: none;
      font-size:   13px;
      font-weight: 500;
      border-radius: 0;
      transition:  color .15s, background .15s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      color:      var(--c-text);
      background: var(--c-accent-lo);
    }

    .sidebar a.active { color: var(--c-accent); }

    .sidebar__count {
      margin-left: auto;
      font-size:   11px;
      color:       var(--c-muted);
      background:  var(--c-bg);
      padding:     1px 6px;
      border-radius: 10px;
    }

    /* ── Main ─────────────────────────────────────────────── */
    /* Fills the area right of the fixed sidebar (no width cap here). */
    main {
      margin-left: var(--sidebar-w);
      padding:     48px 52px;
    }

    /* ── Section ──────────────────────────────────────────── */
    /* Default: comfortable reading width. Add `section--full` to span
       the entire content area (used by the Colors section). */
    .section {
      padding-top:    60px;
      margin-top:     8px;
      max-width:      1100px;
    }

    .section--full { max-width: none; }

    .section__header {
      display:         flex;
      align-items:     baseline;
      gap:             12px;
      margin-bottom:   28px;
      padding-bottom:  16px;
      border-bottom:   1px solid var(--c-border);
    }

    .section__title {
      font-size:   22px;
      font-weight: 700;
      letter-spacing: -.02em;
    }

    .section__sub {
      font-size:  13px;
      color:      var(--c-muted);
    }

    /* ── Color theme group ────────────────────────────────── */
    .color-theme { margin-bottom: 32px; }

    .color-theme__head {
      display:        flex;
      align-items:    baseline;
      gap:            10px;
      margin-bottom:  12px;
    }

    .color-theme__name {
      font-size:   15px;
      font-weight: 600;
      color:       var(--c-text);
    }

    .color-theme__count {
      font-size:   11px;
      color:       var(--c-muted);
      font-family: var(--font-mono);
    }

    /* ── Color grid ───────────────────────────────────────── */
    .color-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 12px;
    }

    .color-swatch {
      border-radius: var(--radius-lg);
      overflow: hidden;
      border: 1px solid var(--c-border);
    }

    .color-swatch__block {
      height: 80px;
      width:  100%;
    }

    .color-swatch__info {
      padding:    10px 12px;
      background: var(--c-surface);
    }

    .color-swatch__name {
      font-size:   12px;
      font-weight: 600;
      color:       var(--c-text);
      margin-bottom: 2px;
      overflow:    hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .color-swatch__value {
      font-family: var(--font-mono);
      font-size:   11px;
      color:       var(--c-muted);
    }

    /* ── Non-hex colour ───────────────────────────────────── */
    .color-swatch--token .color-swatch__block {
      display:     flex;
      align-items: center;
      justify-content: center;
      background:  var(--c-border);
      font-family: var(--font-mono);
      font-size:   10px;
      color:       var(--c-muted);
      padding:     8px;
      text-align:  center;
    }

    /* ── Typography ───────────────────────────────────────── */
    .font-stack {
      background:   var(--c-surface);
      border:       1px solid var(--c-border);
      border-radius: var(--radius-lg);
      padding:      24px 28px;
      margin-bottom: 12px;
    }

    .font-stack__meta {
      display:     flex;
      gap:         12px;
      align-items: baseline;
      margin-bottom: 12px;
    }

    .font-stack__name {
      font-size:   12px;
      font-weight: 600;
      color:       var(--c-accent);
      font-family: var(--font-mono);
    }

    .font-stack__stack {
      font-size:   11px;
      color:       var(--c-muted);
      font-family: var(--font-mono);
    }

    .font-stack__sample {
      font-size:   28px;
      line-height: 1.3;
      color:       var(--c-text);
    }

    /* ── Type ladder (named HTML-element scale) ───────────── */
    .type-ladder__title {
      font-size:      11px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color:          var(--c-muted);
      margin:         28px 0 6px;
    }
    .type-ladder { display: flex; flex-direction: column; }
    .type-row {
      display:        grid;
      grid-template-columns: 132px 1fr;
      align-items:    baseline;
      gap:            20px;
      padding:        12px 0;
      border-bottom:  1px solid var(--c-border);
      overflow:       hidden;
    }
    .type-row:last-child { border-bottom: none; }
    .type-row__meta { display: flex; flex-direction: column; gap: 2px; }
    .type-row__label { font-size: 13px; font-weight: 600; color: var(--c-accent); }
    .type-row__size {
      font-family: var(--font-mono);
      font-size:   10px;
      color:       var(--c-muted);
    }
    .type-row__sample {
      font-family:   "Gotham-Med", "Gradual", system-ui, sans-serif;
      color:         var(--c-text);
      line-height:   1.0;
      white-space:   nowrap;
      overflow:      hidden;
      text-overflow: clip;
    }

    /* ── Spacing scale ────────────────────────────────────── */
    .spacing-table {
      width:           100%;
      border-collapse: collapse;
      font-size:       13px;
    }

    .spacing-table th {
      text-align:    left;
      padding:       8px 12px;
      font-size:     11px;
      text-transform: uppercase;
      letter-spacing: .06em;
      color:         var(--c-muted);
      border-bottom: 1px solid var(--c-border);
    }

    .spacing-table td {
      padding:     10px 12px;
      border-bottom: 1px solid var(--c-border);
      vertical-align: middle;
    }

    .spacing-table tr:last-child td { border-bottom: none; }

    .spacing-table td:first-child {
      font-family: var(--font-mono);
      font-size:   12px;
      color:       var(--c-accent);
    }

    .spacing-table td:nth-child(2) {
      font-family: var(--font-mono);
      font-size:   12px;
      color:       var(--c-muted);
    }

    .spacing-bar {
      height:       10px;
      background:   var(--c-accent);
      border-radius: 2px;
      min-width:    4px;
      opacity:      .7;
    }

    /* ── Layouts ──────────────────────────────────────────── */
    .layout-list {
      display:        flex;
      flex-direction: column;
      gap:            10px;
      margin-bottom:  36px;
    }

    .layout-row {
      display:     flex;
      align-items: center;
      gap:         16px;
    }

    .layout-row__spec {
      font-family: var(--font-mono);
      font-size:   12px;
      color:       var(--c-accent);
      min-width:   160px;
    }

    .layout-preview {
      display: flex;
      gap:     6px;
      flex:    1;
      height:  44px;
    }

    .layout-col {
      display:         flex;
      align-items:     center;
      justify-content: center;
      background:      var(--c-accent-lo);
      border:          1px solid #5b6ef540;
      border-radius:   var(--radius);
      font-family:     var(--font-mono);
      font-size:       11px;
      color:           var(--c-muted);
      min-width:       0;
      overflow:        hidden;
    }

    .layout-themes {
      background:    var(--c-surface);
      border:        1px solid var(--c-border);
      border-radius: var(--radius-lg);
      padding:       16px 20px;
    }

    .layout-themes__label {
      font-size:      10px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color:          var(--c-muted);
      margin-bottom:  10px;
    }

    .layout-theme {
      display:       flex;
      gap:           14px;
      align-items:   baseline;
      padding:       7px 0;
      border-bottom: 1px solid var(--c-border);
    }

    .layout-theme:last-child { border-bottom: none; }

    .layout-theme__value {
      font-family: var(--font-mono);
      font-size:   12px;
      color:       var(--c-info);
      min-width:   170px;
    }

    .layout-theme__text {
      font-size: 13px;
      color:     var(--c-text);
    }

    /* ── Components ───────────────────────────────────────── */
    .component-card {
      background:    var(--c-surface);
      border:        1px solid var(--c-border);
      border-radius: var(--radius-lg);
      padding:       20px 24px;
      margin-bottom: 12px;
    }

    .component-card__header {
      display:     flex;
      align-items: center;
      gap:         10px;
      margin-bottom: 12px;
    }

    .component-card__name {
      font-family:  var(--font-mono);
      font-size:    15px;
      font-weight:  600;
      color:        var(--c-text);
    }

    .component-card__usage {
      font-size:  11px;
      color:      var(--c-muted);
      margin-left: auto;
    }

    .component-card__body {
      display:    flex;
      flex-wrap:  wrap;
      gap:        16px;
    }

    .component-card__group {
      flex:      1 1 200px;
    }

    .component-card__group-label {
      font-size:   10px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color:       var(--c-muted);
      margin-bottom: 6px;
    }

    .token-list {
      display:    flex;
      flex-wrap:  wrap;
      gap:        6px;
    }

    .token {
      font-family:   var(--font-mono);
      font-size:     11px;
      padding:       3px 8px;
      border-radius: 4px;
      background:    var(--c-bg);
      color:         var(--c-text);
      border:        1px solid var(--c-border);
    }

    .token--element  { color: var(--c-info); border-color: #5bc8f530; }
    .token--modifier { color: var(--c-ok);   border-color: #3ecf8e30; }

    .component-card__sources {
      margin-top:  10px;
      font-size:   11px;
      color:       var(--c-muted);
      font-family: var(--font-mono);
    }

    /* ── Badges ───────────────────────────────────────────── */
    .badge {
      font-size:   10px;
      font-weight: 600;
      padding:     2px 7px;
      border-radius: 10px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .badge--ok      { background: #3ecf8e20; color: var(--c-ok); }
    .badge--warning { background: #f5a62320; color: var(--c-warn); }
    .badge--info    { background: #5bc8f520; color: var(--c-info); }

    /* ── Breakpoints ──────────────────────────────────────── */
    .bp-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 12px;
    }

    .bp-card {
      background:    var(--c-surface);
      border:        1px solid var(--c-border);
      border-radius: var(--radius);
      padding:       16px;
    }

    .bp-card__name {
      font-family:  var(--font-mono);
      font-size:    13px;
      color:        var(--c-accent);
      margin-bottom: 4px;
    }

    .bp-card__val {
      font-family: var(--font-mono);
      font-size:   18px;
      font-weight: 700;
      color:       var(--c-text);
    }

    /* ── Footer ───────────────────────────────────────────── */
    .guide-footer {
      margin-top:   60px;
      padding-top:  24px;
      border-top:   1px solid var(--c-border);
      font-size:    12px;
      color:        var(--c-muted);
    }

    /* ── Empty state ──────────────────────────────────────── */
    .empty {
      padding:    32px;
      text-align: center;
      color:      var(--c-muted);
      font-size:  13px;
      background: var(--c-surface);
      border-radius: var(--radius-lg);
      border:     1px dashed var(--c-border);
    }

    /* ── Codey Architecture ───────────────────────────────── */
    .arch-grid {
      display:  grid;
      gap:      24px;
      grid-template-columns: 1fr;
      margin:   24px 0;
    }

    .arch-model {
      background:    var(--c-surface);
      border:        1px solid var(--c-border);
      border-radius: var(--radius-lg);
      padding:       24px;
    }

    .arch-model__title {
      font-size:      14px;
      font-weight:    700;
      color:          var(--c-accent);
      margin-bottom:  8px;
      font-family:    var(--font-mono);
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    .arch-model__desc {
      font-size:  13px;
      line-height: 1.6;
      color:      var(--c-text);
      margin-bottom: 12px;
    }

    .arch-model__example {
      font-size:   12px;
      color:       var(--c-muted);
      padding:     10px 12px;
      background:  var(--c-bg);
      border-left: 2px solid var(--c-accent);
      border-radius: 4px;
      font-style:  italic;
    }

    .axis-visual {
      background:      var(--c-bg);
      border:          1px solid var(--c-border);
      border-radius:   var(--radius);
      padding:         16px;
      margin:          16px 0 12px;
      font-family:     var(--font-mono);
      font-size:       11px;
      color:           var(--c-muted);
      overflow-x:      auto;
      white-space:     pre;
      line-height:     1.5;
    }

    .frame-grid {
      display:  grid;
      gap:      12px;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      margin-top: 20px;
    }

    .frame-card {
      background:    var(--c-surface);
      border:        1px solid var(--c-border);
      border-radius: var(--radius-lg);
      padding:       16px 18px;
    }

    .frame-card__name {
      font-family:  var(--font-mono);
      font-size:    12px;
      color:        var(--c-accent);
      font-weight:  600;
      margin-bottom: 6px;
      text-transform: uppercase;
    }

    .frame-card__label {
      font-size:    13px;
      color:        var(--c-text);
      font-weight:  600;
      margin-bottom: 6px;
    }

    .frame-card__desc {
      font-size:   12px;
      line-height: 1.5;
      color:       var(--c-muted);
      margin-bottom: 8px;
    }

    .frame-card__example {
      font-size:   11px;
      padding:     8px;
      background:  var(--c-bg);
      border-left: 2px solid var(--c-info);
      border-radius: 3px;
      color:       var(--c-info);
      font-style:  italic;
    }

    .arch-summary {
      background:    linear-gradient(135deg, #5b6ef508 0%, transparent 100%);
      border:        1px solid var(--c-accent-lo);
      border-radius: var(--radius-lg);
      padding:       20px 24px;
      margin:        20px 0;
    }

    .arch-summary__title {
      font-size:      12px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color:          var(--c-accent);
      margin-bottom:  8px;
      font-weight:    600;
    }

    .arch-summary__text {
      font-size:  13px;
      color:      var(--c-text);
      line-height: 1.6;
    }

    /* ── Scrollbar ────────────────────────────────────────── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--c-border); border-radius: 3px; }

    /* ── Mobile ───────────────────────────────────────────── */
    /* The fixed left sidebar collapses into a sticky top bar. The brand
       takes a full row (a hard break), then the links wrap onto a second
       row beneath it rather than scrolling horizontally. */
    @media (max-width: 768px) {
      .sidebar {
        position:       sticky;
        top:            0;
        left:           auto;
        width:          100%;
        height:         auto;
        flex-direction: row;
        flex-wrap:      wrap;
        align-items:    stretch;
        gap:            0;
        padding:        0;
        border-right:   none;
        border-bottom:  1px solid var(--c-border);
        z-index:        100;
      }

      .sidebar__brand {
        flex:          1 0 100%;   /* hard break — brand owns row one */
        display:       flex;
        align-items:   baseline;
        gap:           6px;
        padding:       12px 16px;
        margin-bottom: 0;
        border-bottom: 1px solid var(--c-border);
      }

      /* Drop the small "Style Guide" eyebrow to keep the bar compact. */
      .sidebar__project { display: none; }
      .sidebar__version { margin-top: 0; }

      .sidebar a {
        flex:          1 1 auto;   /* links share row two, wrapping as needed */
        justify-content: center;
        white-space:   nowrap;
        padding:       12px 14px;
        border-bottom: 2px solid transparent;
      }

      .sidebar a.active {
        background:           var(--c-accent-lo);
        border-bottom-color:  var(--c-accent);
      }

      /* Counts clutter the wrapped bar — hide them on mobile. */
      .sidebar__count { display: none; }

      main {
        margin-left: 0;
        padding:     24px 20px;
      }

      /* Keep anchored sections clear of the sticky top bar. */
      .section { scroll-margin-top: 96px; }
    }
  </style>
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────────────────────── -->
<nav class="sidebar">
  <div class="sidebar__brand">
    <div class="sidebar__project">Style Guide</div>
    <div class="sidebar__name">Codey.net.au</div>
    <div class="sidebar__version">v1.0.0</div>
  </div>

  <a href="#colors">
    <span>Colors</span>
    <span class="sidebar__count">14</span>
  </a>
  <a href="#typography">
    <span>Typography</span>
    <span class="sidebar__count">6</span>
  </a>
  <a href="#spacing">
    <span>Spacing</span>
    <span class="sidebar__count">34</span>
  </a>
  <a href="#layouts">
    <span>Layouts</span>
    <span class="sidebar__count">6</span>
  </a>
  <a href="#codey-arch">
    <span>Codey Arch</span>
  </a>
  <a href="#breakpoints">
    <span>Breakpoints</span>
    <span class="sidebar__count">5</span>
  </a>
</nav>

<!-- ── Main ───────────────────────────────────────────────────────────────── -->
<main>

  <!-- ── Colors ─────────────────────────────────────────────────────────── -->
  <section class="section section--full" id="colors">
    <div class="section__header">
      <h2 class="section__title">Colors</h2>
      <span class="section__sub">14 tokens · 1 themes</span>
    </div>

    <div class="color-theme">
      <div class="color-theme__head">
        <span class="color-theme__name">Codey</span>
        <span class="color-theme__count">14 colours · .theme-codey</span>
      </div>
      <div class="color-grid">
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(19.24% 0.0153 248.63);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-0</div>
            <div class="color-swatch__value">oklch(19.24% 0.0153 248.63)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(30.44% 0.0462 246.91);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-1</div>
            <div class="color-swatch__value">oklch(30.44% 0.0462 246.91)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(36.04% 0.0616 246.04);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-15</div>
            <div class="color-swatch__value">oklch(36.04% 0.0616 246.04)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(41.64% 0.0771 245.18);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-2</div>
            <div class="color-swatch__value">oklch(41.64% 0.0771 245.18)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(47.24% 0.0925 244.32);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-25</div>
            <div class="color-swatch__value">oklch(47.24% 0.0925 244.32)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(52.83% 0.1080 243.46);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-3</div>
            <div class="color-swatch__value">oklch(52.83% 0.1080 243.46)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(64.03% 0.1388 241.73);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-4</div>
            <div class="color-swatch__value">oklch(64.03% 0.1388 241.73)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(72.66% 0.1387 241.66);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-5</div>
            <div class="color-swatch__value">oklch(72.66% 0.1387 241.66)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(78.73% 0.1074 243.24);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-6</div>
            <div class="color-swatch__value">oklch(78.73% 0.1074 243.24)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(84.80% 0.0762 244.81);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-7</div>
            <div class="color-swatch__value">oklch(84.80% 0.0762 244.81)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(90.87% 0.0449 246.39);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-8</div>
            <div class="color-swatch__value">oklch(90.87% 0.0449 246.39)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(96.94% 0.0137 247.97);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-9</div>
            <div class="color-swatch__value">oklch(96.94% 0.0137 247.97)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(41.64% 0.0771 245.18);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-background</div>
            <div class="color-swatch__value">oklch(41.64% 0.0771 245.18)</div>
          </div>
        </div>
        <div class="color-swatch">
          <div class="color-swatch__block" style="background:oklch(90.87% 0.0449 246.39);"></div>
          <div class="color-swatch__info">
            <div class="color-swatch__name">color-text</div>
            <div class="color-swatch__value">oklch(90.87% 0.0449 246.39)</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── Typography ─────────────────────────────────────────────────────── -->
  <section class="section" id="typography">
    <div class="section__header">
      <h2 class="section__title">Typography</h2>
      <span class="section__sub">6 families · 11-step element scale</span>
    </div>

    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">body</span>
        <span class="font-stack__stack">&quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif</span>
      </div>
      <div class="font-stack__sample" style="font-family: &quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif;">how quickly daft jumping zebras vex!</div>
    </div>
    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">bodymed</span>
        <span class="font-stack__stack">&quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif</span>
      </div>
      <div class="font-stack__sample" style="font-family: &quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif;">how quickly daft jumping zebras vex!</div>
    </div>
    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">head</span>
        <span class="font-stack__stack">&quot;Gradual&quot;,         system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif</span>
      </div>
      <div class="font-stack__sample" style="font-family: &quot;Gradual&quot;,         system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif;">how quickly daft jumping zebras vex!</div>
    </div>
    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">med</span>
        <span class="font-stack__stack">&quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif</span>
      </div>
      <div class="font-stack__sample" style="font-family: &quot;Gotham-Book&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif;">how quickly daft jumping zebras vex!</div>
    </div>
    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">ital</span>
        <span class="font-stack__stack">&quot;Gotham-Ital&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif</span>
      </div>
      <div class="font-stack__sample" style="font-family: &quot;Gotham-Ital&quot;,     system-ui, -apple-system, BlinkMacSystemFont, &#39;Segoe UI&#39;, Roboto, Oxygen, Ubuntu, Cantarell, &#39;Open Sans&#39;, &#39;Helvetica Neue&#39;, sans-serif;">how quickly daft jumping zebras vex!</div>
    </div>
    <div class="font-stack">
      <div class="font-stack__meta">
        <span class="font-stack__name">mono</span>
        <span class="font-stack__stack">ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
                   &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace</span>
      </div>
      <div class="font-stack__sample" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas,
                   &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace;">how quickly daft jumping zebras vex!</div>
    </div>

    <h3 class="type-ladder__title">Type scale — HTML elements</h3>
    <div class="type-ladder">
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">extra-small</span>
          <span class="type-row__size">0.6331rem → 0.7378rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(0.6331rem, 0.7743rem + -0.1821vw, 0.7378rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">small</span>
          <span class="type-row__size">0.844rem → 0.8854rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(0.844rem, 0.8998rem + -0.0721vw, 0.8854rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">p</span>
          <span class="type-row__size">1.0625rem → 1.125rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(1.0625rem, 1.0408rem + 0.1087vw, 1.125rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">h5</span>
          <span class="type-row__size">1.275rem → 1.4996rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(1.275rem, 1.1969rem + 0.3907vw, 1.4996rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">h4</span>
          <span class="type-row__size">1.53rem → 1.999rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(1.53rem, 1.3669rem + 0.8157vw, 1.999rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">h3</span>
          <span class="type-row__size">1.836rem → 2.6647rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(1.836rem, 1.5478rem + 1.4412vw, 2.6647rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">h2</span>
          <span class="type-row__size">2.2032rem → 3.552rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(2.2032rem, 1.7341rem + 2.3457vw, 3.552rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">h1</span>
          <span class="type-row__size">2.6438rem → 4.7348rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(2.6438rem, 1.9165rem + 3.6365vw, 4.7348rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">extra-big</span>
          <span class="type-row__size">3.1726rem → 6.3115rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(3.1726rem, 2.0808rem + 5.459vw, 6.3115rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">super-big</span>
          <span class="type-row__size">3.8071rem → 8.4132rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(3.8071rem, 2.205rem + 8.0106vw, 8.4132rem);">Codey</div>
      </div>
      <div class="type-row">
        <div class="type-row__meta">
          <span class="type-row__label">XXX-big</span>
          <span class="type-row__size">4.5686rem → 11.2149rem</span>
        </div>
        <div class="type-row__sample" style="font-size: clamp(4.5686rem, 2.2568rem + 11.5588vw, 11.2149rem);">Codey</div>
      </div>
    </div>
  </section>

  <!-- ── Spacing ────────────────────────────────────────────────────────── -->
  <section class="section" id="spacing">
    <div class="section__header">
      <h2 class="section__title">Spacing</h2>
      <span class="section__sub">34 values</span>
    </div>

    <table class="spacing-table">
      <thead>
        <tr>
          <th>Token</th>
          <th>Value</th>
          <th>Scale</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>4xs</td>
          <td>clamp(0.1875rem, 0.1658rem + 0.1087vw, 0.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.1875rem, 0.1658rem + 0.1087vw, 0.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>5xs-4xs</td>
          <td>clamp(0.1875rem, 0.1658rem + 0.1087vw, 0.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.1875rem, 0.1658rem + 0.1087vw, 0.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>4xs-3xs</td>
          <td>clamp(0.1875rem, 0.144rem + 0.2174vw, 0.3125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.1875rem, 0.144rem + 0.2174vw, 0.3125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>1</td>
          <td>clamp(0.25rem, 0.2283rem + 0.1087vw, 0.3125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.25rem, 0.2283rem + 0.1087vw, 0.3125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>1-2xs</td>
          <td>clamp(0.25rem, 0.1413rem + 0.5435vw, 0.5625rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.25rem, 0.1413rem + 0.5435vw, 0.5625rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>2</td>
          <td>clamp(0.5625rem, 0.5625rem + 0vw, 0.5625rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.5625rem, 0.5625rem + 0vw, 0.5625rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>2-xs</td>
          <td>clamp(0.5625rem, 0.4538rem + 0.5435vw, 0.875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.5625rem, 0.4538rem + 0.5435vw, 0.875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>3</td>
          <td>clamp(0.8125rem, 0.7908rem + 0.1087vw, 0.875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.8125rem, 0.7908rem + 0.1087vw, 0.875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>3-s</td>
          <td>clamp(0.8125rem, 0.7038rem + 0.5435vw, 1.125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(0.8125rem, 0.7038rem + 0.5435vw, 1.125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>4</td>
          <td>clamp(1.0625rem, 1.0408rem + 0.1087vw, 1.125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(1.0625rem, 1.0408rem + 0.1087vw, 1.125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>4-m</td>
          <td>clamp(1.0625rem, 0.8451rem + 1.087vw, 1.6875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(1.0625rem, 0.8451rem + 1.087vw, 1.6875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>5</td>
          <td>clamp(1.625rem, 1.6033rem + 0.1087vw, 1.6875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(1.625rem, 1.6033rem + 0.1087vw, 1.6875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>5-l</td>
          <td>clamp(1.625rem, 1.4076rem + 1.087vw, 2.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(1.625rem, 1.4076rem + 1.087vw, 2.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>6</td>
          <td>clamp(2.125rem, 2.0815rem + 0.2174vw, 2.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(2.125rem, 2.0815rem + 0.2174vw, 2.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>6-xl</td>
          <td>clamp(2.125rem, 1.6902rem + 2.1739vw, 3.375rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(2.125rem, 1.6902rem + 2.1739vw, 3.375rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>7</td>
          <td>clamp(3.1875rem, 3.1223rem + 0.3261vw, 3.375rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(3.1875rem, 3.1223rem + 0.3261vw, 3.375rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>7-2xl</td>
          <td>clamp(3.1875rem, 2.731rem + 2.2826vw, 4.5rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(3.1875rem, 2.731rem + 2.2826vw, 4.5rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>8</td>
          <td>clamp(4.25rem, 4.163rem + 0.4348vw, 4.5rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(4.25rem, 4.163rem + 0.4348vw, 4.5rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>8-3xl</td>
          <td>clamp(4.25rem, 3.3804rem + 4.3478vw, 6.75rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(4.25rem, 3.3804rem + 4.3478vw, 6.75rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>9</td>
          <td>clamp(6.375rem, 6.2446rem + 0.6522vw, 6.75rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(6.375rem, 6.2446rem + 0.6522vw, 6.75rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>9-4xl</td>
          <td>clamp(6.375rem, 5.8533rem + 2.6087vw, 7.875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(6.375rem, 5.8533rem + 2.6087vw, 7.875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>10</td>
          <td>clamp(7.4375rem, 7.2853rem + 0.7609vw, 7.875rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(7.4375rem, 7.2853rem + 0.7609vw, 7.875rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>10-5xl</td>
          <td>clamp(7.4375rem, 6.894rem + 2.7174vw, 9rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(7.4375rem, 6.894rem + 2.7174vw, 9rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>11</td>
          <td>clamp(8.5rem, 8.3261rem + 0.8696vw, 9rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(8.5rem, 8.3261rem + 0.8696vw, 9rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>11-6xl</td>
          <td>clamp(8.5rem, 7.9348rem + 2.8261vw, 10.125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(8.5rem, 7.9348rem + 2.8261vw, 10.125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>12</td>
          <td>clamp(9.5625rem, 9.3668rem + 0.9783vw, 10.125rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(9.5625rem, 9.3668rem + 0.9783vw, 10.125rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>12-7xl</td>
          <td>clamp(9.5625rem, 8.9755rem + 2.9348vw, 11.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(9.5625rem, 8.9755rem + 2.9348vw, 11.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>13</td>
          <td>clamp(10.625rem, 10.4076rem + 1.087vw, 11.25rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(10.625rem, 10.4076rem + 1.087vw, 11.25rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>13-8xl</td>
          <td>clamp(10.625rem, 10.0163rem + 3.0435vw, 12.375rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(10.625rem, 10.0163rem + 3.0435vw, 12.375rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>14</td>
          <td>clamp(11.6875rem, 11.4484rem + 1.1957vw, 12.375rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(11.6875rem, 11.4484rem + 1.1957vw, 12.375rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>14-9xl</td>
          <td>clamp(11.6875rem, 11.0571rem + 3.1522vw, 13.5rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(11.6875rem, 11.0571rem + 3.1522vw, 13.5rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>15</td>
          <td>clamp(12.75rem, 12.4891rem + 1.3043vw, 13.5rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(12.75rem, 12.4891rem + 1.3043vw, 13.5rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>16</td>
          <td>clamp(13.8125rem, 13.5299rem + 1.413vw, 14.625rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(13.8125rem, 13.5299rem + 1.413vw, 14.625rem), 360px);"></div></td>
        </tr>
        <tr>
          <td>17</td>
          <td>clamp(14.875rem, 14.5707rem + 1.5217vw, 15.75rem)</td>
          <td><div class="spacing-bar" style="width: min(clamp(14.875rem, 14.5707rem + 1.5217vw, 15.75rem), 360px);"></div></td>
        </tr>
      </tbody>
    </table>
  </section>

  <!-- ── Layouts ────────────────────────────────────────────────────────── -->
  <section class="section" id="layouts">
    <div class="section__header">
      <h2 class="section__title">Layouts</h2>
      <span class="section__sub">6 grid patterns · 4 themes</span>
    </div>

    <div class="layout-list">
      <div class="layout-row">
        <div class="layout-row__spec">1&#x2F;1</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 1;"><span>1&#x2F;1</span></div>
        </div>
      </div>
      <div class="layout-row">
        <div class="layout-row__spec">1&#x2F;2, 1&#x2F;2</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 0.5;"><span>1&#x2F;2</span></div>
          <div class="layout-col" style="flex: 0.5;"><span>1&#x2F;2</span></div>
        </div>
      </div>
      <div class="layout-row">
        <div class="layout-row__spec">1&#x2F;3, 1&#x2F;3, 1&#x2F;3</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 0.3333;"><span>1&#x2F;3</span></div>
          <div class="layout-col" style="flex: 0.3333;"><span>1&#x2F;3</span></div>
          <div class="layout-col" style="flex: 0.3333;"><span>1&#x2F;3</span></div>
        </div>
      </div>
      <div class="layout-row">
        <div class="layout-row__spec">1&#x2F;4, 1&#x2F;4, 1&#x2F;4, 1&#x2F;4</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 0.25;"><span>1&#x2F;4</span></div>
          <div class="layout-col" style="flex: 0.25;"><span>1&#x2F;4</span></div>
          <div class="layout-col" style="flex: 0.25;"><span>1&#x2F;4</span></div>
          <div class="layout-col" style="flex: 0.25;"><span>1&#x2F;4</span></div>
        </div>
      </div>
      <div class="layout-row">
        <div class="layout-row__spec">2&#x2F;3, 1&#x2F;3</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 0.6667;"><span>2&#x2F;3</span></div>
          <div class="layout-col" style="flex: 0.3333;"><span>1&#x2F;3</span></div>
        </div>
      </div>
      <div class="layout-row">
        <div class="layout-row__spec">1&#x2F;3, 2&#x2F;3</div>
        <div class="layout-preview">
          <div class="layout-col" style="flex: 0.3333;"><span>1&#x2F;3</span></div>
          <div class="layout-col" style="flex: 0.6667;"><span>2&#x2F;3</span></div>
        </div>
      </div>
    </div>

    <div class="layout-themes">
      <div class="layout-themes__label">Layout themes</div>
      <div class="layout-theme">
        <span class="layout-theme__value">plain-blocks</span>
        <span class="layout-theme__text">Plain grid — no background or padding</span>
      </div>
      <div class="layout-theme">
        <span class="layout-theme__value">plain-blocks-padded</span>
        <span class="layout-theme__text">Plain grid — with padding</span>
      </div>
      <div class="layout-theme">
        <span class="layout-theme__value">card-blocks</span>
        <span class="layout-theme__text">Cards grid — background + padding</span>
      </div>
      <div class="layout-theme">
        <span class="layout-theme__value">full-bleed-grid</span>
        <span class="layout-theme__text">Full bleed — edge to edge, auto-fit columns</span>
      </div>
    </div>
  </section>

  <!-- ── Codey Architecture ─────────────────────────────────────────────── -->
  <section class="section" id="codey-arch">
    <div class="section__header">
      <h2 class="section__title">Codey Architecture</h2>
      <span class="section__sub">Layout foundation & design system</span>
    </div>

    <div class="arch-summary">
      <div class="arch-summary__title">Two-Axis Model</div>
      <div class="arch-summary__text">Vertical (header&#x2F;main&#x2F;footer rows) and horizontal (full&#x2F;content tracks) axes compose independently — they don&#39;t compete.</div>
    </div>

    <!-- Skeleton Axis -->
    <div class="arch-model">
      <div class="arch-model__title">Skeleton Axis (Vertical)</div>
      <div class="arch-model__desc">Vertical document landmarks: header (top), main (fill), footer (bottom) stacked as CSS grid rows.</div>
      <div class="axis-visual">body {
  display: grid;
  grid-template-rows: auto 1fr auto;
}

header { grid-row: 1; }
main   { grid-row: 2; }
footer { grid-row: 3; }</div>
      <div class="arch-model__example">💡 This axis is independent from horizontal layout — every page has this vertical structure.</div>
    </div>

    <!-- Track Axis -->
    <div class="arch-model">
      <div class="arch-model__title">Track Axis (Horizontal)</div>
      <div class="arch-model__desc">Named CSS Grid tracks (full-start, content-start, content-end, full-end) with subgrid propagation for alignment inheritance.</div>
      <div class="axis-visual">.layout {
  grid-template-columns:
    [full-start] minmax(var(--gutter), 1fr)
    [content-start] minmax(0, var(--measure)) [content-end]
    minmax(var(--gutter), 1fr) [full-end];
}

.layout > *      { grid-column: content; }  /* framed */
.layout > .bleed { grid-column: full; }     /* full-width opt-out */</div>
      <div class="arch-model__example">💡 Subgrid propagates these tracks down to all children, keeping horizontal alignment coherent across nesting levels.</div>
    </div>

    <!-- Frame Modes -->
    <div class="arch-model">
      <div class="arch-model__title">Layout Modes</div>
      <div class="arch-model__desc">Five layout modes express different ways to use the track axis. Switch modes via <code style="color: var(--c-accent); font-family: var(--font-mono); font-size: 12px;">data-layout</code> attribute.</div>

      <div class="frame-grid">
        <div class="frame-card">
          <div class="frame-card__name">frame</div>
          <div class="frame-card__label">Frame (Centered)</div>
          <div class="frame-card__desc">Current 80% case refactored; symmetric gutter owned by grid (not body padding), centered max-measure track.</div>
          <div class="frame-card__example">✦ Default framed layout with centered content and symmetric margins.</div>
        </div>
        <div class="frame-card">
          <div class="frame-card__name">bleed</div>
          <div class="frame-card__label">Bleed (Edge-to-edge)</div>
          <div class="frame-card__desc">Full-screen edge-to-edge; --gutter: 0, content supplies local padding.</div>
          <div class="frame-card__example">✦ Full-width backgrounds, hero sections, break-out media.</div>
        </div>
        <div class="frame-card">
          <div class="frame-card__name">spread</div>
          <div class="frame-card__label">Spread (Hybrid)</div>
          <div class="frame-card__desc">Hybrid; framed by default with per-block .bleed opt-outs. Resolves the mode-switch problem on a single page.</div>
          <div class="frame-card__example">✦ Most flexible: frame by default, opt individual blocks into bleed.</div>
        </div>
        <div class="frame-card">
          <div class="frame-card__name">inset</div>
          <div class="frame-card__label">Inset (Content-defines)</div>
          <div class="frame-card__desc">Content-defines-margin; gutter derived from content (asymmetric rail &#x2F; sidebar &#x2F; image sets the column).</div>
          <div class="frame-card__example">✦ Asymmetric layouts with fixed side panels or weighted columns.</div>
        </div>
        <div class="frame-card">
          <div class="frame-card__name">rail</div>
          <div class="frame-card__label">Rail &#x2F; Split (Multi-region)</div>
          <div class="frame-card__desc">Multi-region asymmetric shells (fixed side + fluid main). Enables two-panel layouts.</div>
          <div class="frame-card__example">✦ Account dashboard, docs with sidebar, split-view interfaces.</div>
        </div>
      </div>
    </div>

    <!-- Composition Rule -->
    <div class="arch-model">
      <div class="arch-model__title">Composition Rule</div>
      <div class="arch-model__desc">The skeleton axis (vertical) and track axis (horizontal) <strong>compose, they don't compete</strong>. Every page is the <strong>intersection of one skeleton mode and one track mode</strong>.</div>
      <div class="axis-visual">page = skeleton + tracks (independent axes)

Example: account.php
  skeleton: header/main/footer
  tracks:   spread (frame by default, bleed opt-outs)
  → data-layout="spread" data-pad="narrow"

Example: un-map.php (full-bleed map)
  skeleton: header/main/footer
  tracks:   bleed (everything edge-to-edge)
  → data-layout="bleed"</div>
      <div class="arch-model__example">💡 This orthogonal design means adding new layout modes or skeleton variants doesn't force refactoring the other axis.</div>
    </div>

  </section>

  <!-- ── Components ─────────────────────────────────────────────────────── -->

  <!-- ── Breakpoints ────────────────────────────────────────────────────── -->
  <section class="section" id="breakpoints">
    <div class="section__header">
      <h2 class="section__title">Breakpoints</h2>
      <span class="section__sub">5 screen sizes</span>
    </div>

    <div class="bp-grid">
      <div class="bp-card">
        <div class="bp-card__name">sm</div>
        <div class="bp-card__val">40rem</div>
      </div>
      <div class="bp-card">
        <div class="bp-card__name">md</div>
        <div class="bp-card__val">48rem</div>
      </div>
      <div class="bp-card">
        <div class="bp-card__name">lg</div>
        <div class="bp-card__val">64rem</div>
      </div>
      <div class="bp-card">
        <div class="bp-card__name">xl</div>
        <div class="bp-card__val">80rem</div>
      </div>
      <div class="bp-card">
        <div class="bp-card__name">2xl</div>
        <div class="bp-card__val">96rem</div>
      </div>
    </div>
  </section>

  <!-- ── Footer ─────────────────────────────────────────────────────────── -->
  <footer class="guide-footer">
    Generated 23 July 2026 · Codey.net.au v1.0.0
    · <a href="https:&#x2F;&#x2F;codey.net.au" style="color:inherit;">https:&#x2F;&#x2F;codey.net.au</a>
  </footer>

</main>

<script>
  // Active sidebar link on scroll
  const sections = document.querySelectorAll('.section');
  const links    = document.querySelectorAll('.sidebar a');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        links.forEach(l => l.classList.remove('active'));
        const active = document.querySelector(`.sidebar a[href="#${entry.target.id}"]`);
        if (active) active.classList.add('active');
      }
    });
  }, { rootMargin: '-30% 0px -60% 0px' });

  sections.forEach(s => observer.observe(s));
</script>

</body>
</html>
