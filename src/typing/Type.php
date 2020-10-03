<?php

declare(strict_types=1);

namespace blink\typing;

use JsonSerializable;

/**
 * Class Type
 *
 * @package blink\typing
 */
abstract class Type
{
    /**
     * Returns the name of the type.
     *
     * @return string
     */
    abstract public function getName(): string;

    abstract public function getMetadata(): array;

    /**
     * Returns the text representation of the type.
     *
     * @return string
     */
    public function getDeclaration(): string
    {
        return $this->getName();
    }

    public function toArray(): array
    {
        $array = [
            'type' => $this->getName(),
//            'declaration' => $this->getDeclaration(),
        ];

        if ($metadata = $this->getMetadata()) {
            $array['metadata'] = $metadata;
        }

        return $array;
    }
}
