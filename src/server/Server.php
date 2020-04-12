<?php

namespace blink\server;

use blink\core\Application;
use blink\kernel\Kernel;

/**
 * The base class for Application Server.
 *
 * @package blink\server
 */
abstract class Server extends Kernel
{
    public $host = '0.0.0.0';
    public $port = 7788;

    public $name = 'blink-server';
    public $pidFile;

    /**
     * A php file that application will boot from.
     *
     * @var string
     */
    public $bootstrap;

    /**
     * @return Application
     */
    public function createApplication()
    {
        if ($this->bootstrap instanceof Application) {
            $app = $this->bootstrap;
        } elseif (is_array($this->bootstrap)) {
            $app = new Application($this->bootstrap);
        } else {
            $app = require $this->bootstrap;
        }

        $app->server = $this;

        return $app;
    }

    public function shutdownApplication()
    {
        app()->shutdown();
    }

    public abstract function run();
}
