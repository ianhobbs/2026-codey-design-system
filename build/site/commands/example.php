<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Example',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $page = page($cli->arg('page'));

        // output for the command line
        $cli->success(
            $page->title() . ' ' . $cli->arg('data')
        );

        // output for janitor
        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'message' => $page->title() . ' ' . $cli->arg('data'),
        ]);
    }
];
