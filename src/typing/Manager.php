<?php

declare(strict_types=1);

namespace blink\typing;

use blink\core\InvalidParamException;
use blink\typing\types\AnyType;
use blink\typing\types\DateTimeType;
use blink\typing\types\FloatType;
use blink\typing\types\GenericType;
use blink\typing\types\IntegerType;
use blink\typing\types\ListType;
use blink\typing\types\MapType;
use blink\typing\types\NullType;
use blink\typing\types\StringType;
use blink\typing\types\UnionType;

/**
 * Class Manager
 *
 * @package blink\typing
 */
class Manager
{
    /**
     * @var Type[]
     */
    protected array  $types        = [];
    protected array  $genericTypes = [];
    protected Parser $parser;
    /**
     * @var TypeLoader[]
     */
    protected array $loaders = [];

    public function builtinTypes()
    {
        return [
            NullType::class,
            AnyType::class,
            IntegerType::class,
            FloatType::class,
            StringType::class,
            DateTimeType::class,

            ListType::class,
            MapType::class,
        ];
    }

    public function __construct(array $types = [], array $loaders = [])
    {
        $this->parser  = new Parser($this);
        $this->loaders = $loaders;

        $this->initTypes($types);
    }

    protected function initTypes(array $types): void
    {
        $types = array_merge($this->builtinTypes(), $types);

        foreach ($types as $type) {
            if (is_string($type)) {
                $type = new $type();
            }
            $this->register($type);
        }
    }

    public function genericOf(string $name, array $parameters): GenericType
    {
        $type = $this->getType($name);
        assert($type instanceof GenericType);

        return $type->newInstance($parameters);
    }

    public function unionOf(...$types): Type
    {
        return new UnionType(...$types);
    }

    /**
     * @param Type $type
     */
    public function register(Type $type): void
    {
        $name = $type->getName();

        if (isset($this->types[$name])) {
            throw new InvalidParamException('Duplicated type name: ' . $name);
        }

        $this->types[$name] = $type;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasType(string $name): bool
    {
        return isset($this->types[$name]);
    }

    /**
     * Returns a Type by it's name.
     *
     * @param string $name
     * @return Type
     */
    public function getType(string $name): Type
    {
        $type = $this->types[$name] ?? null;

        if ($type) {
            return $type;
        }

        if ($type = $this->loadType($name)) {
            $this->types[$name] = $type;
            return $type;
        }

        throw new InvalidParamException("Unknown type: " . $name);
    }

    protected function loadType(string $type): ?Type
    {
        foreach ($this->loaders as $loader) {
            if ($type = $loader->loadType($this, $type)) {
                return $type;
            }
        }

        return null;
    }

    public function parse(string $decl): Type
    {
        return $this->parser->parse($decl);
    }
}
