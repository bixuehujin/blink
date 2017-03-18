<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__ . '/web' . $uri)) {
    return false;
}

require getcwd() . '/vendor/autoload.php';

$server = new blink\server\CgiServer([
    'bootstrap' => getcwd() . '/src/bootstrap.php'
]);

$server->run();
