<?php

namespace blink\core;

use Closure;
use blink\di\Container;
use FastRoute;
use blink\log\Logger;
use blink\http\Request;
use blink\http\Response;

/**
 * Class Application
 *
 * @package blink\http
 */
class Application extends ServiceLocator
{
    const VERSION = '0.2.1';

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

    public $controllerNamespace;

    public $currentRequest;

    protected $dispatcher;
    protected $bootstrapped = false;
    protected $refreshing = [];
    protected $lastError;

    public function init()
    {
        if (!$this->root || !file_exists($this->root)) {
            throw new InvalidParamException("The param: 'root' is invalid");
        }

        $this->services = array_merge($this->defaultServices(), $this->services);

        Container::$app = $this;
        Container::$instance = new Container();
    }


    public function bootstrap()
    {
        if (!$this->bootstrapped) {
            try {
                $this->initializeConfig();
                $this->registerServices();
                $this->registerRoutes();
                $this->bootstrapped = true;
                $this->get('log')->info('application started');
            } catch (\Exception $e) {
                if ($this->environment === 'test') {
                    throw $e;
                }

                $this->lastError = $e;
                $this->get('log')->emergency($e);
            } catch (\Throwable $e) {
                if ($this->environment === 'test') {
                    throw $e;
                }

                $this->lastError = $e;
                $this->get('log')->emergency($e);
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
        foreach ($this->services as $id => $definition) {
            $this->bind($id, $definition);
        }

        foreach ($this->services as $id => $_) {
            if ($this->get($id) instanceof ShouldBeRefreshed) {
                $this->refreshing[$id] = true;
            }
        }
    }

    protected function registerRoutes()
    {
        $this->dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            foreach ($this->routes as list($method, $route, $handler)) {
                $r->addRoute($method, $route, $handler);
            }
        });
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

    public function route($method, $route, $handler)
    {
        $this->routes[] = [$method, $route, $handler];

        return $this;
    }

    public function makeRequest($config = [])
    {
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
        if (!$this->bootstrapped) {
            return $this->internalServerError();
        }

        $this->currentRequest = $request;

        /** @var Response $response */
        $response = $this->get('response');

        try {
            $this->exec($request, $response);
        } catch (\Exception $e) {
            $response->data = $e;
            $this->get('errorHandler')->handleException($e);
        } catch (\Throwable $e) {
            $response->data = $e;
            $this->get('errorHandler')->handleException($e);
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
        foreach($this->refreshing as $id => $_) {
            $this->unbind($id);
            $this->bind($id, $this->services[$id]);
        }
    }

    public function handleConsole($input, $output)
    {
        $app = new \blink\core\console\Application([
            'name' => 'Blink Command Runner',
            'version' => self::VERSION,
            'blink' => $this,
        ]);

        $commands = array_merge($this->commands, [
            'blink\console\ServerCommand',
        ]);

        foreach ($commands as $command) {
            $app->add(make(['class' => $command, 'blink' => $this]));
        }

        return $app->run($input, $output);
    }

    protected function exceptionToArray(\Exception $exception)
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
        $info = $this->dispatcher->dispatch($request->method, $request->path);

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
        } else if (($pos = strpos($handler, '@')) !== false) {
            $class = substr($handler, 0, $pos);
            $method = substr($handler, $pos + 1);

            if ($class[0] !== '\\' && $this->controllerNamespace) {
                $class = $this->controllerNamespace . '\\' . $class;
            }

            $action = [$this->get($class), $method];
        } else {
            throw new HttpException(404);
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
