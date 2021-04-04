<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class StructField
 *
 * @package blink\typing\types
 */
class StructField
{
    public string $name;
    public Type $type;
    public array $metadata = [];

    public function __construct(string $name, Type $type, array $metadata = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->metadata = $metadata;
    }

    public function getMetadata(string $name): mixed
    {
        return $this->metadata[$name] ?? null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ];
    }
}
