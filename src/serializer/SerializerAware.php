<?php

declare(strict_types=1);

namespace blink\serializer;

/**
 * Interface SerializerAware
 *
 * @package blink\serializer
 */
interface SerializerAware
{
    public function setSerializer(Serializer $serializer): void;
    public function getSerializer(): Serializer;
}
