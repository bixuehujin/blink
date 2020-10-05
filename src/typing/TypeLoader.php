<?php

declare(strict_types=1);

namespace blink\typing;

/**
 * Interface TypeLoader
 *
 * @package blink\typing
 */
interface TypeLoader
{
    public function loadType(Manager $manager, string $type): ?Type;
}
