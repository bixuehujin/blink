<?php

namespace blink\di\config;

use blink\di\exceptions\NotFoundException;
use blink\di\exceptions\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class ConfigContainer
 *
 * @package blink\di\config
 */
class ConfigContainer implements ContainerInterface
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

    public function apply(array $configMap)
    {
        $this->configMap = array_merge($this->configMap, $configMap);
    }

    /**
     * @param string $name
     * @return ConfigDefinition|null
     */
    public function loadDefinition(string $name): ?ConfigDefinition
    {
        return $this->definitions[$name] ?? null;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws Exception Error while retrieving the entry.
     * @throws NotFoundException No entry was found for **this** identifier.
     */
    public function get($id)
    {
        $definition = $this->loadDefinition($id);

        if (! $definition) {
            throw new NotFoundException("No entry was found for identifier: $id");
        }

        assert($definition instanceof ConfigDefinition);

        $name = $definition->name();

        if ($definition->isRequired() && !array_key_exists($name, $this->configMap)) {
            throw new Exception("The config '$name' is required");
        }

        return $this->configMap[$name] ?? $definition->defaultValue();
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->definitions[$id]);
    }
}
