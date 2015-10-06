<?php

namespace blink\core;

use blink\di\Container;

/**
 * Class ServiceLocator
 *
 * @package blink\core
 */
class ServiceLocator extends Object
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
