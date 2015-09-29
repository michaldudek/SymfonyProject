<?php
require_once __DIR__.'/../.cache/bootstrap.php.cache';

MD\Flavour\Bootstrap\Run::web(
    MyApp\MyApp::class
);
