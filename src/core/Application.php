<?php

namespace blink\core;

use blink\kernel\Kernel;
use blink\routing\Router;
use blink\di\Container;
use blink\log\Logger;
use blink\http\Request;
use blink\http\Response;

/**
 * Class Application
 *
 * @package blink\core
 */
class Application extends Kernel
{
    const VERSION = '0.4.1';

    /**
     * The name for the application.
     *
     * @var string
     */
    public string $name = 'blink';

    /**
     * The root path for the application.
     *
     * @var string
     */
    public string $root;

    /**
     * The installed application plugins.
     *
     * @var array
     */
    public array $plugins = [];

    /**
     * Application service definitions.
     *
     * @var array
     */
    public array $services = [];

    public bool $debug = true;

    /**
     * The environment that the application is running on. dev, prod or test.
     *
     * @var string
     */
    public string $environment = 'dev';

    public string $timezone = 'UTC';

    public string $runtime;

    public $server;

    protected bool $bootstrapped = false;

    protected array $refreshing = [];

    protected $lastError;

    public function init()
    {
        if (!$this->root || !file_exists($this->root)) {
            throw new InvalidParamException("The param: 'root' is invalid");
        }

        Container::$app      = $this;
        Container::$instance = new Container();
    }

    /**
     * @since 0.3
     */
    public function bootstrapIfNeeded()
    {
        if (!$this->bootstrapped) {
            try {
                $this->initializeConfig();
                $this->registerServices();
                $this->registerPlugins();
                $this->bootstrap();
                $this->bootstrapped = true;
            } catch (\Exception $e) {
                throw $e;
//                var_dump($e);
                $this->lastError = $e;
                $this->get('log')
                    ->emergency($e);
            } catch (\Throwable $e) {
                $this->lastError = $e;
                $this->get('log')
                    ->emergency($e);
            }
        }

        return $this;
    }

    protected function initializeConfig()
    {
        date_default_timezone_set($this->timezone);
    }

    protected function registerServices()
    {
        if (is_string($this->services)) {
            $this->services = require $this->services;
        }

        $this->services = array_merge($this->defaultServices(), $this->services);


        foreach ($this->services as $id => $definition) {
            $this->bind($id, $definition);
        }

        foreach ($this->services as $id => $_) {
            if ($this->get($id) instanceof ShouldBeRefreshed) {
                $this->refreshing[$id] = true;
            }
        }
    }

    protected function registerPlugins()
    {
        if (is_string($this->plugins)) {
            $this->plugins = require $this->plugins;
        }

        foreach ($this->plugins as $name => $definition) {
            $this->plugins[$name] = $plugin = $this->getContainer()->make2($definition);
            $plugin->install($this);
        }
    }

    public function defaultServices()
    {
        return [
            'errorHandler' => [
                'class' => ErrorHandler::class,
            ],
            'log'          => [
                'class' => Logger::class,
            ],
            'request'      => [
                'class' => Request::class,
            ],
            'response'     => [
                'class' => Response::class,
            ],
            'router'       => [
                'class' => Router::class,
            ],
        ];
    }

    public function makeRequest($config = [])
    {
        $this->bootstrapIfNeeded();

        $request = $this->get('request');

        foreach ($config as $name => $value) {
            $request->$name = $value;
        }

        return $request;
    }

    public function handle($request)
    {
        /** @var Router $router */
        $router = $this->get('router');
        return $router->handle($request);
    }

    protected function refreshServices()
    {
        foreach ($this->refreshing as $id => $_) {
            $this->unbind($id);
            $this->bind($id, $this->services[$id]);
        }
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {
    }
}
