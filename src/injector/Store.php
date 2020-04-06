<?php


namespace blink\injector;

/**
 * Class Store
 *
 * @package blink\injector
 */
abstract class Store
{
    abstract public function loadDefinition(string $name): ?Definition;
    abstract public function injectOn(Injector $injector, Definition $definition, array $parameters);

    public function getInjector(string $name): ?callable
    {
        $definition = $this->loadDefinition($name);
        if ($definition === null) {
            return null;
        }

        return function (Injector $injector, array $parameters) use ($definition) {
            return $this->injectOn($injector, $definition, $parameters);
        };
    }
}
