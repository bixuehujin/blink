<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * A Enum Type is a type that has a fixed set of values.
 *
 * @package blink\typing\types
 */
class EnumType extends Type
{
    public function __construct(
        protected string $name,
        protected array  $cases,
        protected array  $labels = [],
    ) {
    }

    public static function fromLabels(string $name, array $labels): static
    {
        $values = array_keys($labels);

        return new static($name, $values, $labels);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMetadata(): array
    {
        return [];
    }

    public function getCases(): array
    {
        return $this->cases;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'cases'      => $this->cases,
            'labels' => $this->labels,
        ];
    }
}
