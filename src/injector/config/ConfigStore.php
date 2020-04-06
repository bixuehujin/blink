<?php

namespace blink\injector\config;

use blink\injector\Definition;
use blink\injector\Injector;
use blink\injector\Store;

/**
 * Class ConfigStore
 *
 * @package blink\injector\config
 */
class ConfigStore extends Store
{
    /**
     * @var ConfigDefinition[]
     */
    protected array $definitions = [];
    protected array $configMap   = [];

    public function define(string $name): ConfigDefinition
    {
        return $this->definitions[$name] = $definition = new ConfigDefinition($name);
    }

    public function injectOn(Injector $injector, Definition $definition, array $parameters)
    {
        assert($definition instanceof ConfigDefinition);

        $name = $definition->name();

        if ($definition->isRequired() && !array_key_exists($name, $this->configMap)) {
            throw new \Exception("The config '$name' is required");
        }

        return $this->configMap[$name] ?? $definition->defaultValue();
    }

    public function appendConfigMap(array $configMap)
    {
        $this->configMap = array_merge($this->configMap, $configMap);
    }

    /**
     * @param string $name
     * @return Definition|null
     */
    public function loadDefinition(string $name): ?Definition
    {
        return $this->definitions[$name] ?? null;
    }
}
