<?php

declare(strict_types=1);

namespace blink\serializer\normalizer;

use DateTime;
use blink\typing\Type;
use blink\serializer\Normalizer;

/**
 * Class DateTimeNormalizer
 *
 * @package blink\serializer\normalizer
 */
class DateTimeNormalizer implements Normalizer
{
    public function supportsType(string $type): bool
    {
        return in_array($type, [
            DateTime::class,
        ]);
    }

    public function normalize($data, Type $type)
    {
        return $data->format(DATE_RFC3339);
    }

    public function denormalize($data, Type $type)
    {
        return new DateTime($data);
    }
}
