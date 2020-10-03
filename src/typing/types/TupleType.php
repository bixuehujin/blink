<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class TupleType
 *
 * @package blink\typing\types
 */
class TupleType extends Type
{
    protected array $innerTypes;

    public function __construct(Type ...$innerTypes)
    {
        $this->innerTypes = $innerTypes;
    }

    public function getName(): string
    {
        $names = array_map(fn(Type $type) => $type->getName(), $this->innerTypes);

        return '(' . implode(', ', $names) . ')';
    }

    public function getMetadata(): array
    {
        return [];
    }
}
