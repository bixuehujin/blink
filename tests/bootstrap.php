<?php

use blink\di\Container;

ini_set('error_reporting', E_ALL);

$loader = require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->setAsGlobal();

$store = $container->get(\blink\di\config\ConfigContainer::class);

$store->apply([
    'server.request_class' => \blink\http\Request::class,
    'app.debug' => true,
]);
