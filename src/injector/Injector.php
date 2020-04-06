<?php

namespace blink\injector;

use Exception;

/**
 * Class Injector
 */
class Injector
{
    protected Store $store;
    protected array $loadedItems  = [];
    protected array $loadingItems = [];
    protected array $aliases      = [];

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $name, array $parameters = [])
    {
        $concrete = $this->aliases[$name] ?? $name;
        $injector = $this->store->getInjector($concrete);
        if (!$injector) {
            throw new Exception("Unable to load definition for '$name'");
        }

        if (isset($this->loadingItems[$concrete])) {
            throw new Exception('circular reference');
        }

        $this->loadingItems[$concrete] = true;
        $value                         = $injector($this, $parameters);
        unset($this->loadingItems[$concrete]);
        return $value;
    }

    public function get(string $name)
    {
        if (array_key_exists($name, $this->loadedItems)) {
            return $this->loadedItems[$name];
        }

        return $this->loadedItems[$name] = $this->make($name);
    }

    public function alias(string $name, string $alias)
    {
        $this->aliases[$alias] = $name;
    }
}
