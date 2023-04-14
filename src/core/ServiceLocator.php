<?php

namespace blink\core;

use blink\di\Container;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Class ServiceLocator
 *
 * @package blink\core
 */
class ServiceLocator extends BaseObject
{
    /**
     * Bind a service definition to this service locator.
     *
     * @param $id
     * @param array $definition
     * @param boolean $replace Replace existing services.
     * @throws InvalidConfigException
     */
    public function bind($id, $definition = [], $replace = true)
    {
        $container = Container::$instance;
        if (!$replace && $container->has($id)) {
            throw new InvalidParamException("Can not bind service, service '$id' is already exists.");
        }

        if (is_array($definition) && !isset($definition['class'])) {
            throw new InvalidConfigException("The configuration for the \"$id\" service must contain a \"class\" element.");
        }

        $container->setSingleton($id, $definition);
    }

    public function unbind($id)
    {
        Container::$instance->clear($id);
    }

    public function has($id)
    {
        return Container::$instance->has($id);
    }

    /**
     * Get a service by it's id.
     *
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return Container::$instance->get($id);
    }

    /**
     * Call the given callback or class method with dependency injection.
     *
     * @param callable $callback
     * @param array $arguments
     * @return mixed
     */
    public function call(callable $callback, array $arguments = [])
    {
        $dependencies = $this->getMethodDependencies($callback, array_values($arguments));

        return call_user_func_array($callback, $dependencies);
    }

    protected function getParameterClass(ReflectionParameter $parameter): ?ReflectionClass
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            return new ReflectionClass($type->getName());
        } else {
            return null;
        }
    }

    protected function getMethodDependencies($callback, array $arguments = [])
    {
        $dependencies = $arguments;
        $parameters = array_slice($this->getCallerReflector($callback)->getParameters(), count($arguments));

        foreach ($parameters as $key => $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } elseif ($class = $this->getParameterClass($parameter)) {
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

    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        } else {
            return parent::__get($name);
        }
    }


    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }
}
