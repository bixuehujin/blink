<?php

namespace blink\di;

use blink\core\Configurable;
use blink\di\config\ConfigContainer;
use ReflectionClass;
use blink\di\object\ObjectDefinition;
use Psr\Container\ContainerInterface;
use blink\di\exceptions\Exception;
use blink\di\exceptions\NotFoundException;
use ReflectionException;
use blink\core\InvalidConfigException;

/**
 * Class Container
 */
class Container implements ContainerInterface
{
    /**
     * @var ContainerInterface[]
     */
    protected array $delegates = [];

    protected array $loadedItems  = [];
    protected array $loadingItems = [];
    protected array $aliases      = [];
    /**
     * @var array<ObjectDefinition|null>
     */
    protected array $definitions = [];

    public function __construct(array $delegates = [])
    {
        $configStore = $this->get(ConfigContainer::class);
        $delegates[] = $configStore;

        $this->delegates = $delegates;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param array $config
     * @return mixed
     * @throws Exception
     */
    public function make(string $name, array $parameters = [], array $config = [])
    {
//        $concrete   = $this->aliases[$name] ?? $name;
        $definition = $this->loadDefinition($name);
        if (!$definition) {
            throw new Exception("Unable to load definition for '$name'");
        }

        if (isset($this->loadingItems[$name])) {
//            throw new Exception('circular reference');
        }

        $this->loadingItems[$name] = true;
        $value                         = $this->createObject($definition, $parameters, $config);

        unset($this->loadingItems[$name]);
        return $value;
    }

    /**
     * @param mixed $type
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function make2($type, $arguments = [])
    {
        if (is_string($type)) {
            return $this->make($type, $arguments);
        } else if (is_callable($type)) {
            return $type();
        } else if (is_object($type)) {
            return $type;
        } else if (is_array($type) && isset($type['class'])) {
            $className = $type['class'];
            unset($type['class']);

            return $this->make($className, $arguments, $type);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
        }
    }


    public function alias(string $name, string $alias)
    {
        $this->aliases[$alias] = $name;
    }

    public function define(string $className, ?callable $callback)
    {
        $definition = $this->definitions[$className] = new ObjectDefinition($className);

        if ($callback) {
            $callback($definition);
        }

        return $definition;
    }

    public function extend(string $name, ?callable $callback = null): ?ObjectDefinition
    {
        $definition = $this->loadDefinition($name);

        if ($callback && $definition) {

            $callback($definition);
        }

        return $definition;
    }

    public function withDefinition(string $name): ?ObjectDefinition
    {
        return $this->definitions[$name] = new ObjectDefinition('');
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
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $reference->referenceTo($type->getName());
                } elseif ($type instanceof \ReflectionUnionType) {
                    throw new \RuntimeException("Unable to parse definition, union type is not yet supported for parameter: '{$parameter->getName()}' ");
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $reference->withValue($parameter->getDefaultValue());
                } else {
                    throw new \RuntimeException("Unable to parse definition, missing default value for parameter: '{$parameter->getName()}' ");
                }
            }
        }

        return $definition;
    }

    protected function createObject(ObjectDefinition $definition, array $parameters, array $config = [])
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

            if (is_subclass_of($class, Configurable::class)) {
                $arguments[count($arguments) - 1] = $config;
            }

            $object = new $class(...$arguments);
        }

        $this->injectProperties($object, $definition->getProperties());

        if ($object instanceof ContainerAware) {
            $object->setContainer($this);
        }

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

    public function unset(string $id)
    {
        $id = $this->aliases[$id] ?? $id;
        unset($this->loadedItems[$id]);
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
        $id = $this->aliases[$id] ?? $id;

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

    /**
     * Add a new service provider to the container.
     *
     * @param ServiceProvider|string $provider
     */
    public function add($provider)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = $this->get($provider);
        }

        $provider->register($this);
    }

    public function bind(string $name, $definitions)
    {
        if (is_callable($definitions)) {
            $this->withDefinition($name)->haveFactory($definitions);
        } else if (is_object($definitions)) {
            $this->withDefinition($name)->haveFactory(function () use ($definitions) {
                return $definitions;
            });
        } else {
            $className = $definitions['class'] ?? $name;
            unset($definitions['class']);

            $def = $this->extend($className);

            foreach ($definitions as $key => $value) {
                $def->haveProperty($key)->withValue($value);
            }

            if ($className !== $name) {
                $this->alias($className, $name);
            }
        }
    }
}
