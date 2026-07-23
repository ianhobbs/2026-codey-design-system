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
    ]
];
