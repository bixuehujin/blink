<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\serializer\Normalizer;
use blink\serializer\SerializerAware;
use blink\typing\Type;
use blink\typing\types\ListType;

/**
 * Class ListNormalizer
 *
 * @package blink\serializer\normalizer
 */
class ListNormalizer implements Normalizer, SerializerAware
{
    use NormalizerHelpers;
    use SerializerAwared;

    public function supportsType(string $type): bool
    {
        return $type === 'list';
    }

    public function normalize($data, Type $type)
    {
        assert($type instanceof ListType);

        if (! is_array($data)) {
            throw $this->newTypeError($data, $type);
        }

        $innerType = $type->getInnerType();
        $serializer = $this->getSerializer();

        $results = [];
        foreach ($data as $value) {
            $results[] = $serializer->normalize($value, $innerType);
        }

        return $results;
    }

    public function denormalize($data, Type $type)
    {
        // TODO: Implement denormalize() method.
    }
}
