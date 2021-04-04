<?php

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class UnionType
 *
 * @package blink\typing\types
 */
class UnionType extends Type
{
    /**
     * @var Type[]
     */
    protected array $innerTypes = [];

    public function __construct(Type ...$innerTypes)
    {
        $this->innerTypes = $innerTypes;
    }

    public function getName(): string
    {
        return 'union';
    }

    public function getDeclaration(): string
    {
        $names = array_map(fn (Type $type) => $type->getName(), $this->innerTypes);

        return implode('|', $names);
    }

    public function appendType(Type $type): void
    {
        $this->innerTypes[] = $type;
    }

    public function getMetadata(): array
    {
        return [];
    }

    /**
     * @return Type[]
     */
    public function getInnerTypes(): array
    {
        return $this->innerTypes;
    }
}
