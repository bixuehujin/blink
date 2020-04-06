<?php


namespace blink\injector;

/**
 * Class CompositeStore
 *
 * @package blink\injector
 */
class CompositeStore extends Store
{
    /**
     * @var Store[]
     */
    protected array $stores;

    public function __construct(Store ...$stores)
    {
        $this->stores = $stores;
    }

    public function injectOn(Injector $injector, Definition $definition, array $parameters)
    {
        return null;
    }

    public function getInjector(string $name): ?callable
    {
        foreach ($this->stores as $store) {
            $injector = $store->getInjector($name);
            if ($injector) {
                return $injector;
            }
        }

        return null;
    }

    public function loadDefinition(string $name): ?Definition
    {
        foreach ($this->stores as $store) {
            $definition = $store->loadDefinition($name);
            if ($definition) {
                return $definition;
            }
        }

        return null;
    }
}
