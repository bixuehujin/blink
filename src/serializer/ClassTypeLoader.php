<?php

declare(strict_types=1);

namespace blink\serializer;

use blink\serializer\attributes\Property;
use ReflectionNamedType;
use ReflectionUnionType;
use blink\typing\Manager;
use blink\typing\Type;
use blink\typing\TypeLoader;
use blink\typing\types\StructField;
use blink\typing\types\StructType;

/**
 * Class ClassTypeLoader
 *
 * @package blink\serializer
 */
class ClassTypeLoader implements TypeLoader
{
    public function loadType(Manager $manager, string $name): ?Type
    {
        if (! class_exists($name)) {
            return null;
        }

        $fields = [];
        $class = new \ReflectionClass($name);
        foreach ($class->getProperties() as $property) {
            $type = $property->getType();
            $fieldType = $this->convertReflectionType($manager, $type);
            $metadata = [];

            if (! $property->isPublic()) {
                foreach ($property->getAttributes() as $attribute) {
                    if ($attribute->getName() === Property::class) {
                        $metadata['property'] = $attribute->newInstance();
                        break;
                    }
                }
            }

            $field = new StructField($property->getName(), $fieldType, $metadata);
            $fields[] = $field;
        }

        return new StructType($name, $fields);
    }

    protected function convertReflectionType(Manager $manager, \ReflectionType $type): Type
    {
        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            if ($name == 'int') {
                $name = 'integer';
            }
            if ($type->allowsNull()) {
                $name .= '|null';
            }

            return $manager->parse($name);
        } elseif ($type instanceof ReflectionUnionType) {
            $types = array_map(function (ReflectionNamedType $type) {
                $name = $type->getName();
                if ($name == 'int') {
                    $name = 'integer';
                }
                return $name;
            }, $type->getTypes());

            return $manager->parse(implode('|', $types));
        } else {
            throw new \InvalidArgumentException('Invalid argument of type: ' . get_class($type));
        }
    }
}
