<?php

declare(strict_types=1);

namespace blink\kernel;

use blink\injector\config\ConfigContainer;
use blink\injector\config\ConfigDefinition;
use blink\injector\Container;
use blink\kernel\events\AppInitializing;
use blink\kernel\events\RouteMounting;
use blink\routing\Router;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class Kernel
 *
 * @package blink\kernel
 */
final class Kernel implements EventDispatcherInterface, ListenerProviderInterface
{
    protected ConfigContainer $configContainer;
    protected Container       $container;
    protected Invoker         $invoker;

    protected array $listeners = [];
    /**
     * @var ServiceProvider[]
     */
    protected array $providers = [];

    protected static ?Kernel $instance = null;

    public function __construct()
    {
        $this->configContainer = new ConfigContainer();
        $this->container       = new Container([$this->configContainer]);
    }

    public static function getInstance(): Kernel
    {
        if (self::$instance === null) {
            self::$instance = new Kernel();
        }
        return self::$instance;
    }

    public function define(string $name): ConfigDefinition
    {
        return $this->configContainer->define($name);
    }

    public function set(string $name, $value)
    {
        $this->configContainer->apply([$name => $value]);
    }

    public function bind(string $name, $definitions)
    {
        if (is_callable($definitions)) {
            $this->container->withDefinition($name)->haveFactory($definitions);
        } else if (is_object($definitions)) {
            $this->container->withDefinition($name)->haveFactory(function () use ($definitions) {
                return $definitions;
            });
        } else {
            $className = $definitions['class'] ?? $name;
            unset($definitions['class']);

            $def = $this->container->extend($className);

            foreach ($definitions as $key => $value) {
                $def->haveProperty($key)->withValue($value);
            }

            if ($className !== $name) {
                $this->container->alias($className, $name);
            }
        }
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function unbind(string $name)
    {
        $this->container->unset($name);
    }

    public function add(ServiceProvider $provider)
    {
        $this->providers[] = $provider;
        $provider->register($this);
    }

    public function init()
    {
        $this->dispatch(new AppInitializing($this));
    }

    public function mountRoutes(Router $router)
    {
        $this->dispatch(new RouteMounting($router));
    }

    /**
     * Attach a handler to the given eventClass.
     *
     * @param string $eventClass
     * @param callable $handler
     */
    public function attach(string $eventClass, callable $handler)
    {
        $this->listeners[$eventClass][] = $handler;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function dispatch(object $event)
    {
        $listeners = $this->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            $listener($event);
        }

        return $event;
    }

    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners[get_class($event)] ?? [];
    }
}
