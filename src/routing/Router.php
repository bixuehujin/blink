<?php

declare(strict_types=1);

namespace blink\routing;

use blink\injector\ContainerAware;
use blink\injector\ContainerAwareTrait;
use blink\kernel\Invoker;
use blink\routing\exceptions\MethodNotAllowedException;
use blink\routing\exceptions\RouteNotFoundException;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdParser;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Router
 *
 * @package blink\routing
 */
class Router implements RequestHandlerInterface, ContainerAware
{
    use RouterMethods;
    use ContainerAwareTrait;

    protected ?Dispatcher  $dispatcher = null;
    /**
     * @var Route[]
     */
    protected array $routes = [];

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

    /**
     * @return GroupCountBased
     */
    protected function getDispatcher()
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new GroupCountBased($this->buildRouteData());
        }

        return $this->dispatcher;
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
        $group = new Group($this, $prefix);

        $callback($group);

        return $this;
    }


    /**
     * Add a new route.
     *
     * @param array $verbs
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function addRoute(array $verbs, string $path, $handler): Route
    {
        $route                               = new Route($verbs, $path, $handler);
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
        $route = $this->dispatch($request->getMethod(), $request->getUri()->getPath());

        $invoker = new Invoker($this->getContainer());

        return $invoker->call($route->handler, $route->arguments);
    }
}
