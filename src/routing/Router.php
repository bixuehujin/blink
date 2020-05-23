<?php

declare(strict_types=1);

namespace blink\routing;

use blink\eventbus\EventBus;
use blink\injector\ContainerAware;
use blink\injector\ContainerAwareTrait;
use blink\injector\Invoker;
use blink\routing\events\RouteMounting;
use blink\routing\exceptions\MethodNotAllowedException;
use blink\routing\exceptions\RouteNotFoundException;
use blink\routing\middleware\CallbackHandler;
use blink\routing\middleware\MiddlewareStack;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdParser;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Router
 *
 * @package blink\routing
 */
class Router implements ContainerAware
{
    use RouterMethods;
    use ContainerAwareTrait;

    protected ?Dispatcher  $dispatcher = null;
    /**
     * @var MiddlewareStack
     */
    protected MiddlewareStack $stack;
    protected EventBus        $eventBus;
    /**
     * @var Route[]
     */
    protected array $routes = [];

    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->stack    = new MiddlewareStack();
    }

    protected function buildRouteData(): array
    {
        $collector = new RouteCollector(
            new StdParser(),
            new GroupCountBasedGenerator(),
        );

        foreach ($this->routes as $route) {
            $collector->addRoute($route->verbs, $route->path, spl_object_id($route));
        }

        return $collector->getData();
    }

    protected function getDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new GroupCountBased($this->buildRouteData());
        }

        return $this->dispatcher;
    }

    public function mountRoutes()
    {
        $this->eventBus->dispatch(new RouteMounting($this));
    }

    /**
     * @param MiddlewareInterface|string $middleware
     */
    public function use($middleware)
    {
        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $this->getContainer()->get($middleware);
        }

        $this->stack->add($middleware);
    }

    /**
     * Add a group of routes with a callback.
     *
     * @param string $prefix
     * @param callable $callback
     * @return $this
     */
    public function group(string $prefix, callable $callback): self
    {
        $group = new Group($this, $this->stack, $prefix);

        $callback($group);

        return $this;
    }

    /**
     * Add a new route.
     *
     * @param MiddlewareStack $stack
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function addRoute(MiddlewareStack $stack, array $verbs, string $path, $handler): Route
    {
        $route = new Route($stack, $verbs, $path, $handler);

        $this->routes[spl_object_id($route)] = $route;

        return $route;
    }

    /**
     * @param string $verb
     * @param string $path
     * @return Route
     */
    public function dispatch(string $verb, string $path): Route
    {
        $info = $this->getDispatcher()->dispatch($verb, $path);

        if ($info[0] === Dispatcher::NOT_FOUND) {
            throw new RouteNotFoundException($path);
        } else if ($info[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException("$verb method is not allowed for $path", $info[1]);
        }

        $route = $this->routes[$info[1]];
        return $route->withArguments($info[2]);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $route   = $this->dispatch($request->getMethod(), $request->getUri()->getPath());
            $stack   = $route->stack;
            $handler = new CallbackHandler(function () use ($route) {
                $invoker = new Invoker($this->getContainer());
                return $invoker->call($route->handler, $route->arguments);
            });
            $stack->setDefaultHandler($handler);
        } catch (\Throwable $exception) {
            $stack = $this->stack;
            $stack->setDefaultHandler(new CallbackHandler(function () use ($exception) {
                throw $exception;
            }));
        }

        return $stack->handle($request);
    }
}
