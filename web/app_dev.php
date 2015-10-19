<?php
require_once __DIR__.'/../.cache/bootstrap.php.cache';

// check access to this script
MD\Flavour\Bootstrap\Blocker::check(
    $_SERVER,
    [
        // put allowed IP addresses in this array
    ]
);

// run in web mode
MD\Flavour\Bootstrap\Run::web(
    Project\ProjectApp::class,
    'dev',
    true
);
