<?php

declare(strict_types=1);

namespace blink\kernel;

use blink\core\BaseObject;
use blink\core\InvalidParamException;
use blink\injector\config\ConfigContainer;
use blink\injector\config\ConfigDefinition;
use blink\injector\Container;

abstract class Kernel extends BaseObject
{
    protected Container       $container;
    protected ConfigContainer $configContainer;
    protected Invoker         $invoker;
    /**
     * @var ServiceProvider[]
     */
    protected array $providers = [];

    public function __construct($config = [])
    {
        $this->configContainer = new ConfigContainer();
        $this->container       = new Container([$this->configContainer]);
        $this->invoker         = new Invoker($this->container);

        parent::__construct($config);
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
    }

    public function bootstrap()
    {
        foreach ($this->providers as $provider) {
            $provider->register($this);
        }
    }

    public function getInvoker(): Invoker
    {
        return $this->invoker;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
