<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use blink\serializer\Serializer;

/**
 * Trait SerializerAwared
 *
 * @package blink\serializer\normalizer
 */
trait SerializerAwared
{
    protected Serializer $serializer;

    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }
}
