<?php

declare(strict_types=1);

namespace blink\serializer;

use blink\core\InvalidParamException;
use blink\support\Json;
use blink\typing\Registry;
use blink\typing\Type;
use blink\typing\types\AnyType;

/**
 * Class Serializer
 *
 * @package blink\serializer
 */
class Serializer
{
    protected Registry $typing;
    /**
     * @var Normalizer[]
     */
    protected array $normalizers = [];

    /**
     * @var Normalizer[]
     */
    protected array $normalizerMap = [];

    /**
     * Serializer constructor.
     *
     * @param Registry $typing
     * @param Normalizer[] $normalizers
     */
    public function __construct(Registry $typing, array $normalizers)
    {
        $this->typing = $typing;

        foreach ($normalizers as $normalizer) {
            if ($normalizer instanceof SerializerAware) {
                $normalizer->setSerializer($this);
            }
            $this->normalizers[] = $normalizer;
        }
    }

    protected function getNormalizer(string $type): Normalizer
    {
        $result = $this->normalizerMap[$type] ?? null;

        if (! $result) {
            foreach ($this->normalizers as $normalizer) {
                if ($normalizer->supportsType($type)) {
                    $result = $this->normalizerMap[$type] = $normalizer;
                    break;
                }
            }
        }

        if (! $result) {
            throw new InvalidParamException("No normalizer found for: $type");
        }

        return $result;
    }

    public function getTyping(): Registry
    {
        return $this->typing;
    }

    protected function getTypeInfo(string $class): Type
    {
    }

    /**
     * @param mixed $data
     * @param Type $type
     * @return mixed
     */
    public function normalize($data, Type $type): mixed
    {
        if ($type instanceof AnyType) {
            return $data;
        }

        return $this->getNormalizer($type->getName())->normalize($data, $type);
    }

    /**
     * @param mixed $data
     * @param Type $type
     * @return mixed
     */
    public function denormalize(mixed $data, Type $type): mixed
    {
    }

    /**
     * @param mixed $data
     * @param Type|string $type
     * @return string
     */
    public function serialize(mixed $data, Type|string $type): string
    {
        if (is_string($type)) {
            $type = $this->typing->parse($type);
        }

        $normalized = $this->normalize($data, $type);

        return Json::encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param mixed $source
     * @param Type|string $type
     * @return mixed
     */
    public function deserialize(mixed $source, Type|string $type)
    {
        if (is_string($type)) {
            $type = $this->typing->parse($type);
        }

        return $this->denormalize($source, $type);
    }
}
