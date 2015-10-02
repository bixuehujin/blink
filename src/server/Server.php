<?php

namespace blink\server;

use blink\Blink;
use blink\base\Object;
use blink\http\Application;

/**
 * The base class for Application Server.
 *
 * @package blink\server
 */
abstract class Server extends Object
{
    public $host = '0.0.0.0';
    public $port = 7788;

    /**
     * A php file that application will boot from.
     *
     * @var string
     */
    public $bootstrap;

    public function startApp()
    {
        if ($this->bootstrap instanceof Application) {
            $app = $this->bootstrap;
        } else if (is_array($this->bootstrap)) {
            $app = new Application($this->bootstrap);
        } else {
            $app = require $this->bootstrap;
        }

        Blink::$app = $app->bootstrap();
    }

    public function stopApp()
    {
        Blink::$app->shutdown();
    }

    public function handleRequest($request)
    {
        return Blink::$app->handleRequest($request);
    }

    abstract public function run();
}
