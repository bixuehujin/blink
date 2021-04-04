<?php

declare(strict_types=1);

namespace blink\serializer;

use blink\serializer\attributes\ComputedProperty;
use blink\serializer\attributes\Property;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use blink\typing\Registry;
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
    public function loadType(Registry $registry, string $name): ?Type
    {
        if (! class_exists($name)) {
            return null;
        }

        $class = new ReflectionClass($name);

        return new StructType($name, array_merge(
            $this->loadFromProperties($registry, $class),
            $this->loadFromMethods($registry, $class),
        ));
    }

    /**
     * @param Registry $registry
     * @param ReflectionClass $class
     * @return StructField[]
     */
    protected function loadFromProperties(Registry $registry, ReflectionClass $class): array
    {
        $fields = [];

        foreach ($class->getProperties() as $property) {
            $propertyName = $property->getName();
            $type = $property->getType();
            $fieldType = $this->convertReflectionType($registry, $type);
            $metadata = [];
            $defaultProperties = $class->getDefaultProperties();

            $metadata['property'] = ($property->getAttributes(Property::class)[0] ?? null)
                    ?->newInstance() ?? new Property();

            $metadata['property']->name = $propertyName;
            $metadata['property']->guarded = ! $property->isPublic();

            if (array_key_exists($propertyName, $defaultProperties)) {
                $metadata['property']->hasDefault = true;
                $metadata['property']->defaultValue = $defaultProperties[$propertyName];
            } else {
                $metadata['property']->hasDefault = false;
            }

            $field = new StructField($property->getName(), $fieldType, $metadata);
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @param Registry $registry
     * @param ReflectionClass $class
     * @return StructField[]
     */
    protected function loadFromMethods(Registry $registry, ReflectionClass $class): array
    {
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $fields = [];
        foreach ($methods as $method) {
            /** @var ReflectionAttribute|null $attribute */
            $attribute = $method->getAttributes(ComputedProperty::class)[0] ?? null;
            if ($attribute) {
                /** @var ComputedProperty $property */
                $property = $attribute->newInstance();
                $property->getter = $method->getName();
                $type = $method->getReturnType();
                $fieldType = $this->convertReflectionType($registry, $type);
                $field = new StructField($property->name, $fieldType, [
                    'property' => $property,
                ]);
                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected function convertReflectionType(Registry $registry, ReflectionType $type): Type
    {
        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            if ($name == 'int') {
                $name = 'integer';
            }
            if ($type->allowsNull()) {
                $name .= '|null';
            }

            return $registry->parse($name);
        } elseif ($type instanceof ReflectionUnionType) {
            $types = array_map(function (ReflectionNamedType $type) {
                $name = $type->getName();
                if ($name == 'int') {
                    $name = 'integer';
                }
                return $name;
            }, $type->getTypes());

            return $registry->parse(implode('|', $types));
        } else {
            throw new \InvalidArgumentException('Invalid argument of type: ' . get_class($type));
        }
    }
}
