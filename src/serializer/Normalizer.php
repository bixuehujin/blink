<?php

declare(strict_types=1);

namespace blink\serializer;

use blink\typing\Type;

/**
 * Interface Normalizer
 *
 * @package blink\serializer
 */
interface Normalizer
{
    public function supportsType(string $type): bool;

    /**
     * @param mixed $data
     * @param Type $type
     * @return mixed
     */
    public function normalize($data, Type $type);

    /**
     * @param mixed $data
     * @param Type $type
     * @return mixed
     */
    public function denormalize($data, Type $type);
}
