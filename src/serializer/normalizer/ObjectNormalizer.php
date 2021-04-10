<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\serializer\attributes\ComputedProperty;
use blink\serializer\Normalizer;
use blink\serializer\SerializerAware;
use blink\typing\Type;
use blink\typing\types\StructField;
use blink\typing\types\StructType;
use blink\serializer\attributes\Property;

/**
 * Class ObjectNormalizer
 *
 * @package blink\serializer\normalizer
 */
class ObjectNormalizer implements Normalizer, SerializerAware
{
    use SerializerAwared;
    use NormalizerHelpers;

    public function supportsType(string $type): bool
    {
        $typing = $this->serializer->getTyping();

        if (! $typing->hasType($type)) {
            return false;
        }

        $type = $typing->getType($type);

        return $type instanceof StructType;
    }

    protected function fetchObjectField(object $object, StructField $field)
    {
        $property = $field->getMetadata('property');
        if ($property) {
            $getter = '';
            if ($property instanceof ComputedProperty) {
                $getter = $property->getter;
            } elseif ($property instanceof Property) {
                $getter = $property->getter === true ? 'get' . $property->name : $property->getter;
            }

            if ($getter) {
                $value = $object->$getter();
            } else {
                $value = $object->{$field->name};
            }
            return $value;
        } else {
            return $object->{$field->name};
        }
    }

    public function normalize($data, Type $type)
    {
        assert($type instanceof StructType);

        $result = [];
        foreach ($type->fields() as $fieldType) {
            $value = $this->fetchObjectField($data, $fieldType);

            $result[$fieldType->name] = $this->serializer->normalize($value, $fieldType->type);
        }

        if ($result === []) {
            return new \stdClass();
        }

        return $result;
    }

    public function denormalize($data, Type $type)
    {
    }
}
