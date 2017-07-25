<?php

namespace blink\core;

use Closure;
use blink\di\Container;
use FastRoute;
use blink\log\Logger;
use blink\http\Request;
use blink\http\Response;
use blink\console\ShellCommand;
use blink\console\ServerCommand;
use blink\console\ServerReloadCommand;
use blink\console\ServerRestartCommand;
use blink\console\ServerServeCommand;
use blink\console\ServerStartCommand;
use blink\console\ServerStopCommand;

/**
 * Class Application
 *
 * @package blink\http
 */
class Application extends ServiceLocator
{
    const VERSION = '0.4.0 (dev)';

    /**
     * The name for the application.
     *
     * @var string
     */
    public $name = 'blink';

    /**
     * The root path for the application.
     *
     * @var string
     */
    public $root;

    /**
     * The installed application plugins.
     *
     * @var array
     */
    public $plugins = [];

    /**
     * Available console commands.
     *
     * @var string[]
     */
    public $commands = [];

    public $routes = [];

    /**
     * Application service definitions.
     *
     * @var array
     */
    public $services = [];

    public $debug = true;

    /**
     * The environment that the application is running on. dev, prod or test.
     *
     * @var string
     */
    public $environment = 'dev';

    public $timezone = 'UTC';

    public $runtime;

    public $server;

    public $controllerNamespace;

    public $currentRequest;

    protected $dispatcher;

    protected $bootstrapped = false;

    protected $refreshing = [];

    protected $lastError;

    protected $router;

    public function init()
    {
        if (!$this->root || !file_exists($this->root)) {
            throw new InvalidParamException("The param: 'root' is invalid");
        }

        $this->router = $this->createRouter();

        Container::$app = $this;
        Container::$instance = new Container();
    }

    /**
     * @deprecated
     */
    public function bootstrap()
    {
        $this->bootstrapIfNeeded();
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
                $this->registerRoutes();
                $this->bootstrapped = true;
            } catch (\Exception $e) {
                if ($this->environment === 'test') {
                    throw $e;
                }

                $this->lastError = $e;
                $this->get('log')
                     ->emergency($e);
            } catch (\Throwable $e) {
                if ($this->environment === 'test') {
                    throw $e;
                }

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
            $this->plugins[$name] = $plugin = make($definition);
            $plugin->install($this);
        }
    }

    protected function createRouter()
    {
        return new FastRoute\RouteCollector(
            new FastRoute\RouteParser\Std(),
            new FastRoute\DataGenerator\GroupCountBased()
        );
    }

    protected function registerRoutes()
    {
        if (is_string($this->routes)) {
            $this->routes = require $this->routes;
        }

        foreach ($this->routes as $value) {
            if (!is_array($value[0]) && is_array($value[1])) {
                $this->group($value[0], $value[1]);
            } else {
                $this->route($value[0], $value[1], $value[2]);
            }
        }
    }

    /**
     * @return FastRoute\Dispatcher\GroupCountBased
     */
    public function getDispatcher()
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new FastRoute\Dispatcher\GroupCountBased($this->router->getData());
        }

        return $this->dispatcher;
    }

    public function defaultServices()
    {
        return [
            'errorHandler' => [
                'class' => ErrorHandler::class,
            ],
            'log' => [
                'class' => Logger::class,
            ],
            'request' => [
                'class' => Request::class,
            ],
            'response' => [
                'class' => Response::class,
            ],
        ];
    }

    /**
     * Returns the default console commands definitions.
     *
     * @return array
     */
    public function defaultCommands()
    {
        return [
            'server' => [
                'class' => ServerCommand::class,
            ],
            'server:start' => [
                'class' => ServerStartCommand::class,
            ],
            'server:stop' => [
                'class' => ServerStopCommand::class,
            ],
            'server:restart' => [
                'class' => ServerRestartCommand::class,
            ],
            'server:reload' => [
                'class' => ServerReloadCommand::class,
            ],
            'server:serve' => [
                'class' => ServerServeCommand::class,
            ],
            'shell' => [
                'class' => ShellCommand::class,
            ]
        ];
    }

    /**
     * Returns all console commands definitions.
     *
     * @return array
     */
    public function consoleCommands()
    {
        return array_merge_recursive($this->defaultCommands(), $this->commands);
    }

    public function route($method, $route, $handler)
    {
        $this->router->addRoute($method, $route, $handler);

        return $this;
    }

    public function group($group, $routes)
    {
        $this->router->addGroup($group, function (FastRoute\RouteCollector $router) use ($routes) {
            foreach ($routes as list($method, $route, $handler)) {
                $router->addRoute($method, $route, $handler);
            }
        });

        return $this;
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

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function handleRequest($request)
    {
        if ($this->lastError) {
            return $this->internalServerError();
        }

        $this->currentRequest = $request;

        /** @var Response $response */
        $response = $this->get('response');

        try {
            $this->exec($request, $response);
        } catch (\Exception $e) {
            $response->data = $e;
            $this->get('errorHandler')
                 ->handleException($e);
        } catch (\Throwable $e) {
            $response->data = $e;
            $this->get('errorHandler')
                 ->handleException($e);
        }

        try {
            $response->callMiddleware();
        } catch (\Exception $e) {
            $response->data = $e;
        } catch (\Throwable $e) {
            $response->data = $e;
        }

        $this->formatException($response->data, $response);

        $response->prepare();
        $this->refreshServices();

        $this->currentRequest = null;

        return $response;
    }

    protected function internalServerError()
    {
        $response = new Response([
            'data' => $this->lastError ?: new HttpException(500, 'There was an internal server error'),
        ]);

        $this->formatException($response->data, $response);

        $response->prepare();

        return $response;
    }

    protected function formatException($e, $response)
    {
        if (!$response->data instanceof \Exception && !$response->data instanceof \Throwable) {
            return;
        }

        if ($e instanceof HttpException) {
            $response->status($e->statusCode);
            $response->data = $this->exceptionToArray($e);
        } else {
            if ($this->environment === 'test') {
                throw $e;
            }

            $response->status(500);
            $response->data = $this->exceptionToArray($e);
        }
    }

    protected function exec($request, $response)
    {
        list($handler, $args) = $this->dispatch($request);

        $action = $this->createAction($handler);

        $request->callMiddleware();

        $data = $this->runAction($action, $args, $request, $response);

        if (!$data instanceof Response && $data !== null) {
            $response->with($data);
        }
    }

    protected function refreshServices()
    {
        foreach ($this->refreshing as $id => $_) {
            $this->unbind($id);
            $this->bind($id, $this->services[$id]);
        }
    }

    protected function exceptionToArray($exception)
    {
        $array = [
            'name' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }
        if ($this->debug) {
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['trace'] = explode("\n", $exception->getTraceAsString());
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->exceptionToArray($prev);
        }

        return $array;
    }

    protected function dispatch($request)
    {
        $info = $this->getDispatcher()->dispatch($request->method, $request->path);

        switch ($info[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                throw new HttpException(404);
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $info[1];
                throw new HttpException(405);
            case FastRoute\Dispatcher::FOUND:

                return [$info[1], $info[2]];
        }
    }

    protected function createAction($handler)
    {
        if ($handler instanceof Closure) {
            $action = $handler;
        } else {
            if (($pos = strpos($handler, '@')) !== false) {
                $class = substr($handler, 0, $pos);
                $method = substr($handler, $pos + 1);

                if ($class[0] !== '\\' && $this->controllerNamespace) {
                    $class = $this->controllerNamespace . '\\' . $class;
                    $class = strtr($class, '/', '\\');
                }

                $action = [$this->get($class), $method];
            } else {
                throw new HttpException(404);
            }
        }

        return $action;
    }

    protected function runAction($action, $args, $request, $response)
    {
        $this->beforeAction($action, $request);

        $data = $this->call($action, $args);

        $this->afterAction($action, $request, $response);

        return $data;
    }

    protected function beforeAction($action, $request)
    {
        if ($action instanceof Closure) {
            return;
        }

        list($object, $method) = $action;

        if (method_exists($object, 'before')) {
            call_user_func([$object, 'before'], $method, $request);
        }
    }

    protected function afterAction($action, $request, $response)
    {
        if ($action instanceof Closure) {
            return;
        }

        list($object, $method) = $action;

        if (method_exists($object, 'after')) {
            call_user_func([$object, 'after'], $method, $request, $response);
        }
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {
    }
}
