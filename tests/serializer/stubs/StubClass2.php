<?php

declare(strict_types=1);

namespace blink\tests\serializer\stubs;

use blink\serializer\attributes\Property;

/**
 * Class StubClass2
 *
 * @package blink\tests\serializer\stubs
 */
class StubClass2
{
    #[Property(guarded: true)]
    protected int $a;

    #[Property(setter: 'setB')]
    protected int $b;

    #[Property(getter: 'getC')]
    protected int $c;

    protected int $d = 1;
}
