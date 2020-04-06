<?php

namespace blink\injector;

use ReflectionClass;
use blink\injector\object\ObjectDefinition;
use blink\injector\object\Method;
use Psr\Container\ContainerInterface;
use blink\injector\exceptions\Exception;
use blink\injector\exceptions\NotFoundException;
use ReflectionException;

/**
 * Class Container
 */
class Container implements ContainerInterface
{
    /**
     * @var ContainerInterface[]
     */
    protected $delegates = [];

    protected array $loadedItems  = [];
    protected array $loadingItems = [];
    protected array $aliases      = [];

    public function __construct(array $delegates)
    {
        $this->delegates = $delegates;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $name, array $parameters = [])
    {
        $concrete   = $this->aliases[$name] ?? $name;
        $definition = $this->loadDefinition($concrete);
        if (!$definition) {
            throw new Exception("Unable to load definition for '$name'");
        }

        if (isset($this->loadingItems[$concrete])) {
            throw new Exception('circular reference');
        }

        $this->loadingItems[$concrete] = true;
        $value                         = $this->createObject($definition, $parameters);

        unset($this->loadingItems[$concrete]);
        return $value;
    }

    public function alias(string $name, string $alias)
    {
        $this->aliases[$alias] = $name;
    }

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

    public function loadDefinition(string $name): ?ObjectDefinition
    {
        if (array_key_exists($name, $this->definitions)) {
            return $this->definitions[$name];
        }

        if (!class_exists($name)) {
            return $this->definitions[$name] = null;
        }

        return $this->definitions[$name] = $this->parseDefinition($name, new ReflectionClass($name));
    }

    protected function parseDefinition(string $name, \ReflectionClass $reflector)
    {
        $definition = new ObjectDefinition($name);
        $method     = $reflector->getConstructor();
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

    protected function createObject(ObjectDefinition $definition, array $parameters)
    {
        if ($factory = $definition->getFactory()) {
            return $factory($this);
        }

        $class = $definition->name();
        $constructor = $definition->getConstructor();

        if ($constructor === null) {
            $object = new $class();
        } else {
            $arguments = [];
            foreach ($constructor->getArguments() as $reference) {
                if ($className = $reference->getReferentName()) {
                    $arguments[] = $this->get($className);
                } else {
                    $arguments[] = $reference->getValue();
                }
            }

            $object = new $class(...$arguments);
        }

        $this->injectProperties($object, $definition->getProperties());

        return $object;
    }

    /**
     * @param object $object
     * @param Reference[] $properties
     * @throws Exception
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function injectProperties(object $object, array $properties)
    {
        foreach ($properties as $property) {
            $name = $property->getName();
            if ($referentName = $property->getReferentName()) {
                $value = $this->get($referentName);
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
        $definition = $this->loadDefinition($id);
        if ($definition) {
            return true;
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($id)) {
                return true;
            }
        }

        return false;
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
        if (array_key_exists($id, $this->loadedItems)) {
            return $this->loadedItems[$id];
        }

        if ($this->loadDefinition($id)) {
            return $this->loadedItems[$id] = $this->make($id);
        }

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($id)) {
                return $delegate->get($id);
            }
        }

        throw new NotFoundException("No entry was found for identifier: $id");
    }
}
