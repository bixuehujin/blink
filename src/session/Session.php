<?php

namespace blink\session;

use blink\core\BaseObject;
use blink\support\BagTrait;

/**
 * The container of session key-value pairs.
 *
 * @package blink\session
 */
class Session extends BaseObject
{
    use BagTrait;

    /**
     * The id of the session, this is possible null when the session is not actually stored.
     *
     * @var string|null
     */
    public $id;

    public function __construct(array $attributes = [], $config = [])
    {
        $this->replace($attributes);

        parent::__construct($config);
    }
}
