<?php

declare(strict_types=1);

namespace blink\injector\object;

use blink\injector\Definition;
use blink\injector\Injector;
use blink\injector\Reference;
use blink\injector\Store;

/**
 * Class ObjectCreator
 *
 * @package blink\injector\object
 */
class ObjectCreator extends Store
{
    /**
     * @var array<ObjectDefinition|null>
     */
    protected array $definitions = [];

    public function define(string $className, ?callable $callback)
    {
        $definition = $this->definitions[$className] = new ObjectDefinition($className);

        if ($callback) {
            $callback($definition);
        }

        return $definition;
    }

    public function extend(string $name, ?callable $callback): ?ObjectDefinition
    {
        $definition = $this->loadDefinition($name);

        if ($callback && $definition) {

            $callback($definition);
        }

        assert($definition instanceof ObjectDefinition);

        return $definition;
    }

    public function loadDefinition(string $name): ?Definition
    {
        if (array_key_exists($name, $this->definitions)) {
            return $this->definitions[$name];
        }

        if (! class_exists($name)) {
            return $this->definitions[$name] = null;
        }

        return $this->definitions[$name] = $this->parseDefinition($name, new \ReflectionClass($name));
    }

    protected function parseDefinition(string $name, \ReflectionClass $reflector)
    {
        $definition = new ObjectDefinition($name);
        $method = $reflector->getConstructor();
        if ($method) {
            $constructor = $definition->haveConstructor();
            foreach ($method->getParameters() as $parameter) {
                $reference = $constructor->haveArgument($parameter->getName());
                if ($refClass = $parameter->getClass()) {
                    $reference->referenceTo($refClass->getName());
                } else if ($parameter->isDefaultValueAvailable()) {
                    $reference->withValue($parameter->getDefaultValue());
                } else {
                    throw new \RuntimeException("Unable to parse definition, missing default value for parameter: '{$parameter->getName()}' ");
                }
            }
        }

        return $definition;
    }

    protected function createObject(Injector $injector, string $class, ?Method $constructor)
    {
        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];
        foreach ($constructor->getArguments() as $reference) {
            if ($className = $reference->getReferentName()) {
                $arguments[] = $injector->get($className);
            } else {
                $arguments[] = $reference->getValue();
            }
        }

        return new $class(...$arguments);
    }

    /**
     * @param Injector $injector
     * @param object $object
     * @param Reference[] $properties
     */
    protected function injectProperties(Injector $injector, object $object, array $properties)
    {
        foreach ($properties as $property) {
            $name = $property->getName();
            if ($referentName = $property->getReferentName()) {
                $value = $injector->get($referentName);
            } else {
                $value = $property->getValue();
            }

            if ($property->isGuarded()) {
                $reflector = new \ReflectionProperty($object, $name);
                $reflector->setAccessible(true);
                $reflector->setValue($object, $value);
            } else {
                $object->$name = $value;
            }
        }
    }

    public function injectOn(Injector $injector, Definition $definition, array $parameters)
    {
        assert($definition instanceof ObjectDefinition);

        if ($factory = $definition->getFactory()) {
            return $factory($injector);
        }

        $object = $this->createObject($injector, $definition->name(), $definition->getConstructor());

        $this->injectProperties($injector, $object, $definition->getProperties());

        return $object;
    }
}
