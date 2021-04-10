<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\serializer\Normalizer;
use blink\typing\Type;
use blink\typing\types\FloatType;
use blink\typing\types\IntegerType;
use blink\typing\types\NullType;
use blink\typing\types\StringType;

/**
 * Class ScalarNormalizer
 *
 * @package blink\serializer\normalizer
 */
class ScalarNormalizer implements Normalizer
{
    use NormalizerHelpers;

    public function supportsType(string $type): bool
    {
        return in_array($type, [
            'null',
            'integer',
            'float',
            'string',
        ]);
    }

    protected function checkType($data, Type $type)
    {
        if ($type instanceof NullType) {
            if ($data !== null) {
                throw $this->newTypeError($data, $type);
            }
        } elseif ($type instanceof IntegerType) {
            if (! is_integer($data)) {
                throw $this->newTypeError($data, $type);
            }
        } elseif ($type instanceof FloatType) {
            if (! is_float($data) && !is_integer($data)) {
                throw $this->newTypeError($data, $type);
            }
        } elseif ($type instanceof StringType) {
            if (! is_string($data)) {
                throw $this->newTypeError($data, $type);
            }
        } else {
            throw $this->newTypeError($data, $type);
        }
    }

    public function normalize($data, Type $type)
    {
        $this->checkType($data, $type);

        return $data;
    }

    public function denormalize($data, Type $type)
    {
        // TODO: Implement denormalize() method.
    }
}
