<?php

namespace blink\http;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use blink\core\BaseObject;
use blink\support\BagTrait;

class HeaderBag extends BaseObject implements IteratorAggregate, Countable, ArrayAccess, JsonSerializable
{
    use BagTrait;

    public function __construct(array $data = [], $config = [])
    {
        $this->replace($data);

        parent::__construct($config);
    }

    protected function transformKey($key)
    {
        return strtr(strtolower($key), '_', '-');
    }

    protected function transformValue($value)
    {
        return (array)$value;
    }

    public function with($key, $values)
    {
        $values = array_merge($this->get($key, []), $this->transformValue($values));

        $this->set($key, $values);
    }

    public function first($key, $default = null)
    {
        $values = $this->get($key);

        return !empty($values) ? array_shift($values) : $default;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->all();
    }
}
