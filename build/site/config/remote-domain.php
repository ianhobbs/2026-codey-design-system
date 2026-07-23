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
    'debug'   => false,
    'salt' => 'bhuoiyohoiuknpewmi902D',
    'panel'   => [
        'install' => true, 
        'vue' => [
        'compiler' => false  // allow creating the first Panel account at /panel
        ],
    ]
];
