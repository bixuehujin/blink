<?php

declare(strict_types=1);

namespace blink\typing\types;

use blink\typing\Type;

/**
 * Class ListType
 *
 * @package blink\typing\types
 */
class ListType extends GenericType
{
    public function getName(): string
    {
        return 'list';
    }

    public function getInnerType(): Type
    {
        return $this->parameterTypes[0];
    }

    public function allowedParameters(): int
    {
        return 1;
    }
}
