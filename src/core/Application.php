<?php

namespace blink\core;

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
    const VERSION = '0.1.0 (dev)';

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

    /**
     * Specify the services that need to be refreshed after request.
     *
     * @var array
     */
    public $refresh = ['request', 'response'];

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

    protected $dispatcher;
    protected $bootstrapped = false;


    public function init()
    {
        if (!$this->root || !file_exists($this->root)) {
            throw new InvalidParamException("The param: 'root' is invalid");
        }

        $this->services += $this->defaultServices();
        Container::$app = $this;
        Container::$instance = new Container();
    }


    public function bootstrap()
    {
        if (!$this->bootstrapped) {
            $this->initializeConfig();
            $this->registerServices();
            $this->registerRoutes();
            $this->bootstrapped = true;

            $this->get('log')->info('application started');
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

    public function handleRequest($request)
    {
        $response = $this->get('response');

        try {
            $this->exec($request, $response);
        } catch (HttpException $e) {
            $response->status($e->statusCode);
            $response->data = $this->exceptionToArray($e);
        } catch (\Exception $e) {
            if ($this->environment === 'test') {
                throw $e;
            }

            $this->get('errorHandler')->handleException($e);

            $response->status(500);
            $response->data = $this->exceptionToArray($e);
        }

        $response->prepare();

        $this->afterRequest();

        return $response;
    }

    protected function exec($request, $response)
    {
        list($handler, $args) = $this->dispatch($request);

        $data = $this->callAction($handler, $args);

        if (!$data instanceof Response && $data) {
            $response->with($data);
        }
    }

    protected function afterRequest()
    {
        foreach($this->refresh as $id) {
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
            'blink\console\ServeCommand',
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

    protected function callAction($handler, $args)
    {
        if (is_callable($handler)) {
            $data = $this->call($handler, $args);
        } else if (($pos = strpos($handler, '@')) !== false) {
            $class = substr($handler, 0, $pos);
            $method = substr($handler, $pos + 1);

            if ($class[0] !== '\\' && $this->controllerNamespace) {
                $class = $this->controllerNamespace . '\\' . $class;
            }

            $data = $this->call([$this->get($class), $method], $args);
        } else {
            throw new HttpException(404);
        }

        return $data;
    }

    /**
     * Shutdown the application.
     */
    public function shutdown()
    {

    }
}
