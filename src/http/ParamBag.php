<?php

namespace blink\http;

use Countable;
use IteratorAggregate;
use blink\core\Object;
use blink\support\BagTrait;

class ParamBag extends Object implements IteratorAggregate, Countable
{
    use BagTrait;

    public function __construct(array $data = [], $config = [])
    {
        $this->replace($data);

        parent::__construct($config);
    }

    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    public function integer($key, $default = 0)
    {
        return (int) $this->get($key, $default);
    }

    public function boolean($key, $default = false)
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }
}
