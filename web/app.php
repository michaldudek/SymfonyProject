<?php

use Symfony\Component\HttpFoundation\Request;

use MyApp\MyApp;

require_once __DIR__.'/../.cache/bootstrap.php.cache';

$kernel = new MyApp('prod', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
