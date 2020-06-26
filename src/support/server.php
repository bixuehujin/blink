<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__ . '/web' . $uri)) {
    return false;
}

require getcwd() . '/vendor/autoload.php';

$container = new \blink\di\Container();

require getcwd() . '/src/bootstrap.php';

$server = $container->get(\blink\server\CgiServer::class);
$server->run();
