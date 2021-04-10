<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\support\Json;
use blink\typing\Type;

/**
 * Trait NormalizerHelpers
 *
 * @package blink\serializer\normalizer
 */
trait NormalizerHelpers
{
    protected function getPhpType($data): string
    {
        $type = get_debug_type($data);

        if ($type === 'int') {
            return 'integer';
        }

        return $type;
    }

    /**
     * @param mixed $data
     * @param Type $type
     * @return \Exception
     */
    protected function newTypeError($data, Type $type)
    {
        return new \InvalidArgumentException(sprintf(
            "Unexpected type error, unable to normalize type of '%s' to '%s'",
            $this->getPhpType($data),
            $type->getDeclaration(),
        ));
    }
}
