<?php

namespace blink\http;

use blink\Blink;
use FastRoute;
use blink\base\ErrorHandler;
use blink\di\ServiceLocator;
use blink\base\HttpException;
use blink\log\Logger;

/**
 * Class Application
 *
 * @package blink\http
 */
class Application extends ServiceLocator
{
    const VERSION = '0.1.0 (dev)';

    public $name = 'blink';
    public $routes = [];
    public $services = [];
    public $debug = false;
    public $timezone = 'UTC';
    public $runtimePath;

    protected $dispatcher;
    protected $bootstrapped = false;


    public function bootstrap()
    {
        if (!$this->bootstrapped) {
            $this->initializeConfig();
            $this->registerServices();
            $this->registerRoutes();
            $this->bootstrapped = true;
        }

        return $this;
    }

    protected function initializeConfig()
    {
        date_default_timezone_set($this->timezone);
    }

    protected function registerServices()
    {
        $services = $this->services + $this->defaultServices();

        foreach ($services as $id => $definition) {
            $this->set($id, $definition);
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
        ];
    }

    public function route($method, $route, $handler)
    {
        $this->routes[] = [$method, $route, $handler];

        return $this;
    }

    public function handleRequest($request)
    {
        $response = new Response();

        try {
            $this->exec($request, $response);
        } catch (HttpException $e) {
            $response->status($e->statusCode);
            $response->data = [
                'code' => $e->statusCode,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $this->get('errorHandler')->handleException($e);

            $response->status(500);

            $data = [
                'code' => $e->getCode(),
                'message' => get_class($e) . ': ' . $e->getMessage(),
            ];

            if (!$this->debug) {
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();
                $data['trace'] = $e->getTrace();
            }

            $response->data = $data;
        }

        return $response;
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

    protected function exec($request, $response)
    {
        list($handler, $args) = $this->dispatch($request);

        if (is_callable($handler)) {
            $reflection = new \ReflectionFunction($handler);
            $args = $this->resolveParameters($args, $reflection->getParameters(), $request, $response);
            $data = call_user_func_array($handler, $args);
        } else if (($pos = strpos($handler, ':')) !== false) {
            $class = substr($handler, 0, $pos);
            $method = substr($handler, $pos + 1);

            $reflection = new \ReflectionClass($class);
            $parameters = $reflection->getConstructor()->getParameters();

            $obj = $reflection->newInstanceArgs($this->resolveParameters([], $parameters, $request, $response));

            $reflection = new \ReflectionMethod($class, $method);
            $args = $this->resolveParameters($args, $reflection->getParameters(), $request, $response);

            $data = call_user_func_array([$obj, $method], $args);
        } else {
            throw new HttpException(404);
        }

        if (!$data instanceof Response && $data) {
            $response->with($data);
        }
    }

    protected function resolveParameters($args, $parameters, $request, $response)
    {
        $parameters = array_slice($parameters, count($args));

        foreach ($parameters as $parameter) {
            $type = $parameter->getClass();
            if (!$type) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            if ($request instanceof $type->name) {
                $args[] = $request;
            } elseif ($response instanceof $type->name) {
                $args[] = $response;
            } else {
                $args[] = $parameter->getDefaultValue();
            }
        }

        return $args;
    }
}
