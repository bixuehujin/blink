<?php

namespace blink\session;

use blink\core\Object;
use blink\support\BagTrait;

/**
 * Class SessionBag
 *
 * @package blink\session
 */
class SessionBag extends Object
{
    use BagTrait;

    public function __construct(array $attributes = [], $config = [])
    {
        $this->replace($attributes);

        parent::__construct($config);
    }
}
