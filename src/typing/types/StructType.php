<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class StructType
 *
 * @package blink\typing\types
 */
class StructType extends Type
{
    protected string $name;
    protected array $fields;

    public function __construct(string $name, array $fields)
    {
        $this->name   = $name;
        $this->fields = $fields;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): array
    {
        return [];
    }

    /**
     * @return StructField[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function toArray(): array
    {
        $fields = array_map(fn (StructField $field) => $field->toArray(), $this->fields());

        return parent::toArray() + ['fields' => $fields];
    }
}
