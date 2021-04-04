<?php

namespace blink\server;

use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use blink\routing\Router;

/**
 * The base class for Application Server.
 *
 * @package blink\server
 */
abstract class Server implements ContainerAware
{
    use ContainerAwareTrait;

    public string $host = '0.0.0.0';
    public int    $port = 7788;

    public string $name = 'blink-server';
    public string $pidFile = '';

    public function getRouter(): Router
    {
        return $this->getContainer()->get(Router::class);
    }

    abstract public function run();
}
