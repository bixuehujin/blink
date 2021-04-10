<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\serializer\Normalizer;
use blink\serializer\SerializerAware;
use blink\typing\Type;
use blink\typing\types\UnionType;

/**
 * Class UnionNormalizer
 *
 * @package blink\serializer\normalizer
 */
class UnionNormalizer implements Normalizer, SerializerAware
{
    use NormalizerHelpers;
    use SerializerAwared;

    public function supportsType(string $type): bool
    {
        return $type === 'union';
    }

    public function normalize($data, Type $type)
    {
        assert($type instanceof UnionType);

        $dataType = $this->getPhpType($data);

        foreach ($type->getInnerTypes() as $innerType) {
            if ($innerType->getName() == $dataType) {
                return $this->serializer->serialize($data, $innerType);
            }
        }

        throw $this->newTypeError($data, $type);
    }

    public function denormalize($data, Type $type)
    {
        // TODO: Implement denormalize() method.
    }
}
