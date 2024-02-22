<?php

namespace blink\di;

use blink\core\Configurable;
use blink\core\InvalidParamException;
use blink\di\attributes\Inject;
use blink\di\config\ConfigContainer;
use blink\server\SwServer;
use chalk\components\client\Platform;
use ReflectionClass;
use blink\di\object\ObjectDefinition;
use Psr\Container\ContainerInterface;
use blink\di\exceptions\Exception;
use blink\di\exceptions\NotFoundException;
use ReflectionException;
use blink\core\InvalidConfigException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

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

    public static Container $global;

    public function __construct(array $delegates = [])
    {
        $configStore = $this->get(ConfigContainer::class);
        $delegates[] = $configStore;

        $this->delegates = $delegates;
    }

    /**
     * @param class-string<T> $name
     * @param array $parameters
     * @param array $config
     * @return T
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
            throw new Exception('circular reference detected on making ' . $name);
        }

        $this->loadingItems[$name] = true;

        if ($factory = $definition->getFactory()) {
            $value = $factory(); 
        } else {
            $value = $this->createObject($definition, $parameters, $config);
        }

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
        } elseif (is_callable($type)) {
            return $type();
        } elseif (is_object($type)) {
            return $type;
        } elseif (is_array($type) && isset($type['class'])) {
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
        $constructor     = $reflector->getConstructor();
        if ($constructor) {
            $this->parseConstructor($definition, $constructor);
        }

        $filter = ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        foreach ($reflector->getProperties($filter) as $property) {
            $this->parseProperty($definition, $property, $reflector->getDefaultProperties());
        }

        return $definition;
    }


    protected function parseConstructor(ObjectDefinition $definition, ReflectionMethod $method): void
    {
        $constructor = $definition->haveConstructor();
        foreach ($method->getParameters() as $parameter) {
            $reference = $constructor->haveArgument($parameter->getName());
            $type = $parameter->getType();
            if ($referentName = $this->getInjectableType($type)) {
                $reference->referenceTo($referentName);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $reference->withDefault($parameter->getDefaultValue());
            } else {
                $reference->setRequired(true);
            }
        }
    }

    protected function parseProperty(ObjectDefinition $definition, ReflectionProperty $property, array $defaultProperties): void
    {
        $attributes = $property->getAttributes();
        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Inject::class) {
                $reference = $definition->haveProperty($property->getName());
                $reference->guarded(! $property->isPublic());
                /** @var Inject $injectInfo */
                $injectInfo = $attribute->newInstance();
                $referentName = $injectInfo->getReference();
                if ($referentName === null) {
                    $referentName = $this->getInjectableType($property->getType());
                    if (! $referentName) {
                        throw new \RuntimeException("Unable to parse definition, unable to detect reference to inject for property: '{$property->getName()}' ");
                    }
                }

                $reference->referenceTo($referentName);

                if ($setter = $injectInfo->getSetter()) {
                    $reference->withSetter($setter);
                }

                if (array_key_exists($property->getName(), $defaultProperties)) {
                    $reference->withDefault($defaultProperties[$property->getName()]);
                }
            }
        }
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
                    $arguments[] = $parameters[$className] ?? $this->get($className);
                } else {
                    $arguments[] = $parameters[$reference->getName()] ?? $reference->getDefault();
                }
            }

            if (is_subclass_of($class, Configurable::class)) {
                $configKey = $class . '.params';
                if ($this->has($configKey)) {
                    $config = array_merge($this->get($configKey), $config);
                }
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
                $value = $property->getDefault();
            }

            if ($setter = $property->getSetter()) {
                $object->$setter($value);
            } elseif ($property->isGuarded()) {
                $reflector = new ReflectionProperty($object, $name);
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
        } elseif (is_object($definitions)) {
            $this->withDefinition($name)->haveFactory(function () use ($definitions) {
                return $definitions;
            });
        } else {
            $className = $definitions['class'] ?? $name;
            unset($definitions['class']);

            $def = $this->extend($className);

            foreach ($definitions as $key => $value) {
                $def->haveProperty($key)->withDefault($value);
            }

            if ($className !== $name) {
                $this->alias($className, $name);
            }
        }
    }

    /**
     * Call the given callback or class method with dependency injection.
     *
     * @param callable $callback
     * @param array $arguments
     * @return mixed
     */
    public function call($callback, $arguments = [])
    {
        $caller = $this->getCallerReflector($callback);

        if (is_array($callback) && count($callback) === 2 && $caller instanceof ReflectionMethod && !$caller->isStatic()) {
            $callback[0] = $this->make($callback[0]);
        }

        $parameters   = $caller->getParameters();
        $dependencies = $this->getMethodDependencies($parameters, $arguments);

        return call_user_func_array($callback, $dependencies);
    }

    public function setAsGlobal(): void
    {
        self::$global = $this;
    }

    /**
     * @param ReflectionParameter[] $parameters
     * @param array $arguments
     * @return array
     */
    protected function getMethodDependencies(array $parameters, array $arguments = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $dependencies[] = $arguments[$name];
            } elseif ($typeName = $this->getInjectableType($parameter->getType())) {
                $dependencies[] = $arguments[$typeName] ?? $this->get($typeName);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
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
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        return new ReflectionFunction($callback);
    }

    protected function getInjectableType(?ReflectionType $type): ?string
    {
        if ($type === null || $type->isBuiltin()) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        } else {
            return null;
        }
    }
}
