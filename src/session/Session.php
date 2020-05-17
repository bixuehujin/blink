<?php

declare(strict_types=1);

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
    public ?string $id;

    public function __construct(array $attributes = [], array $config = [])
    {
        $this->replace($attributes);

        parent::__construct($config);
    }
}
