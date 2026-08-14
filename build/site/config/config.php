<?php

use JohannSchopplich\Helpers\Env;

/**
 * Kirby config — STARTER.
 *
 * Add a domain-name.config with some settings turned off for production
 *
 * Secrets come from glue/, which sits OUTSIDE the web root and is gitignored
 * except for .env.example. Nothing secret is hardcoded here — a starter that
 * ships a real cookie key hands every clone the same one.
 * */
$glue = dirname(__DIR__, 3) . '/glue';

// Dotenv's repository is immutable — first value wins — so the most specific
// file loads first. .env.local is dev-only and never deployed; .env.production
// only exists on the server; .env is the shared baseline.
foreach (['.env.local', '.env.production', '.env'] as $envFile) {
    if (is_file($glue . '/' . $envFile)) {
        Env::load($glue, $envFile);
    }
}

// Cookie::$key is typed string — only override when the env actually supplies
// one, so a fresh clone without glue/.env still boots (on Kirby's weak default).
if ($cookieKey = Env::get('KIRBY_COOKIE_KEY')) {
    Kirby\Http\Cookie::$key = $cookieKey;
}

return [
    'johannschopplich.helpers.env.path' => $glue,

    // Local development conveniences — turn OFF for production.
    'debug'   => true,
    'editor' => 'vscode',
    // Kirby 5 reads content.salt, not salt — the old top-level key was a no-op.
    'content.salt' => Env::get('KIRBY_SALT'),
    'panel'   => [
        'install' => true, 
        'vue' => [
        'compiler' => false  // allow creating the first Panel account at /panel
        ],
    ],
        'thumbs' => [
        'srcsets' => [
            'default' => [
                '400w' => ['width' => 400],
                '600w' => ['width' => 600],
                '900w' => ['width' => 900],
                '1200w' => ['width' => 1200],
                '1800w' => ['width' => 1800]
            ],
            'webp' => [
                '400w' => ['width' => 400, 'format' => 'webp', 'quality' => 75],
                '600w' => ['width' => 600, 'format' => 'webp', 'quality' => 75],
                '900w' => ['width' => 900, 'format' => 'webp', 'quality' => 75],
                '1200w' => ['width' => 1200, 'format' => 'webp', 'quality' => 75],
                '1800w' => ['width' => 1800, 'format' => 'webp', 'quality' => 75]
            ],
            'avif' => [
                '400w' => ['width' => 400, 'format' => 'avif', 'quality' => 60],
                '600w' => ['width' => 600, 'format' => 'avif', 'quality' => 60],
                '900w' => ['width' => 900, 'format' => 'avif', 'quality' => 60],
                '1200w' => ['width' => 1200, 'format' => 'avif', 'quality' => 60],
                '1800w' => ['width' => 1800, 'format' => 'avif', 'quality' => 60]
            ],
            'webp-sq' => [
                '400w' => ['width' => 400, 'height' => 400, 'format' => 'webp', 'quality' => 70, 'crop' => true],
                '600w' => ['width' => 600, 'height' => 600, 'format' => 'webp', 'quality' => 70, 'crop' => true],
                '900w' => ['width' => 900, 'height' => 900, 'format' => 'webp', 'quality' => 70, 'crop' => true],
                '1200w' => ['width' => 1200, 'height' => 1200, 'format' => 'webp', 'quality' => 70, 'crop' => true]
            ],
            'mobilehero' => [
                '400w' => ['width' => 400, 'format' => 'webp', 'crop' => true],
                '600w' => ['width' => 600, 'format' => 'webp', 'crop' => true],
                '900w' => ['width' => 900, 'format' => 'webp', 'crop' => true],
                '1200w' => ['width' => 1200, 'format' => 'webp', 'crop' => true],
                '1800w' => ['width' => 1800, 'format' => 'webp', 'crop' => true]
            ],
        ]
    ],
    'tobimori.seo.canonicalBase' => 'https://yourdomain.org',
    'tobimori.seo.lang' => 'en_US',
    'tobimori.seo.robots' => [
            'active' => false,
            'content' => [
                '*' => [
                    'Allow' => ['/'],
                    'Disallow' => ['/kirby', '/panel', '/content']
                ]
            ]
    ],
        'tobimori.seo.sitemap' => [
        'groupByTemplate' => false, // Create separate sitemaps for each template type
        'excludeTemplates' => ['error'], // Exclude templates from sitemap
        'changefreq' => 'monthly', // Change frequency, can be a string or a function
        'priority' => fn (Page $p) => number_format(($p->isHomePage()) ? 1 : max(1 - 0.2 * $p->depth(), 0.2), 1), 
    ],
        'akibeo.csp' => [
        'enabled' => true,

        // Optional: only send the header on these hosts (compared
        // lowercase, without port). Empty array = all hosts.
        // 'hosts' => ['www.example.com'],
        'hosts' => [],

        // Optional: test the policy without enforcing it — sends
        // Content-Security-Policy-Report-Only instead.
        'reportOnly' => true,

        // Only needed when Kirby's pages cache is enabled — it is not here.
        // Leaving it on runs the placeholder swap on every response, and with
        // komments rendering user-supplied HTML, anyone who writes the
        // placeholder string into a comment gets a live nonce reflected back.
        // If the pages cache is ever switched on, re-enable this AND set a
        // random per-site 'cacheSafePlaceholder' (the default is public).
        'cacheSafe' => false,
    ]
];
