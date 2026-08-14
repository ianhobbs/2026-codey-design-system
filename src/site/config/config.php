<?php

/**
 * Kirby config — STARTER. 
 * 
 * Add a domain-name.config with some settings turned off for production
 * 
 * */
Kirby\Http\Cookie::$key = 'uibhbuouyyei8198Bhy';

return [
    // Local development conveniences — turn OFF for production.
    'debug'   => true,
    'editor' => 'vscode',
    'salt' => 'bhuoiyohoiuknpewmi902D',
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
