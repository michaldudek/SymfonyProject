<?php
require_once __DIR__.'/../.cache/bootstrap.php.cache';

// check access to this script
MD\Flavour\Bootstrap\Blocker::check($_SERVER);

// run in web mode
MD\Flavour\Bootstrap\Run::web(
    MyApp\MyApp::class,
    'dev',
    true
);
