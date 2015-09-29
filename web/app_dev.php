<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use MD\Flavour\Bootstrap\Blocker;

use MyApp\MyApp;

require_once __DIR__.'/../.cache/bootstrap.php.cache';

// check access to this script
Blocker::check($_SERVER);

Debug::enable();

$kernel = new MyApp('dev', true);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
