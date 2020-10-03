<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\SyntaxException;
use blink\typing\Type;

/**
 * Class GenericType
 *
 * @package blink\typing\types
 */
abstract class GenericType extends Type
{
    protected array $parameterTypes;

    public function getDeclaration(): string
    {
        $innerNames = array_map(fn(Type $type) => $type->getName(), $this->parameterTypes);

        return $this->getName(). '<' . implode(', ', $innerNames) . '>';
    }

    abstract public function allowedParameters(): int;

    /**
     * @param Type[] $parameters
     * @return Type
     * @throws SyntaxException
     */
    public function newInstance(array $parameters): GenericType
    {
        $allowedParameters = $this->allowedParameters();
        $givenParameters = count($parameters);
        $name = $this->getName();

        if ($givenParameters !== $allowedParameters) {
            throw new SyntaxException("The generic type: $name accepts $allowedParameters parameters, $givenParameters was given");
        }

        $type = clone $this;
        $type->parameterTypes = $parameters;

        return $type;
    }

    public function getMetadata(): array
    {
        return [];
    }
}
