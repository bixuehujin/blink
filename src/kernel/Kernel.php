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
    /**
     * @var ServiceProvider[]
     */
    protected array $providers = [];

    public function __construct($config = [])
    {
        $this->configContainer = new ConfigContainer();
        $this->container       = new Container([$this->configContainer]);

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

    /**
     * Call the given callback or class method with dependency injection.
     *
     * @param $callback
     * @param array $arguments
     * @return mixed
     */
    public function call($callback, $arguments = [])
    {
        $dependencies = $this->getMethodDependencies($callback, $arguments);

        return call_user_func_array($callback, $dependencies);
    }

    protected function getMethodDependencies($callback, array $arguments = [])
    {
        $dependencies = $arguments;
        $parameters = array_slice($this->getCallerReflector($callback)->getParameters(), count($arguments));

        foreach ($parameters as $key => $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } elseif ($class = $parameter->getClass()) {
                $dependencies[] = $this->get($class->getName());
            } else {
                throw new InvalidParamException('Missing required argument: ' . $parameter->getName());
            }
        }

        return $dependencies;
    }

    protected function getCallerReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        if (is_array($callback)) {
            return new \ReflectionMethod($callback[0], $callback[1]);
        }

        return new \ReflectionFunction($callback);
    }

    public function bootstrap()
    {
        foreach ($this->providers as $provider) {
            $provider->register($this);
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
