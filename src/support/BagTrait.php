<?php

namespace blink\support;

use ArrayIterator;
use Traversable;

trait BagTrait
{
    private $data;

    protected function transformKey($key)
    {
        return $key;
    }

    protected function transformValue($value)
    {
        return $value;
    }

    public function all()
    {
        return $this->data;
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function replace(array $data = [])
    {
        $this->data = [];
        $this->add($data);
    }

    public function add(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->data[$this->transformKey($key)] = $this->transformValue($value);
        }
    }

    public function get($key, $default = null)
    {
        $key = $this->transformKey($key);

        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($this->transformKey($key));
        }

        return $results;
    }

    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->data;

        foreach ($keys as $key) {
            unset($results[$this->transformKey($key)]);
        }

        return $results;
    }

    public function set($key, $value)
    {
        $this->data[$this->transformKey($key)] = $this->transformValue($value);
    }

    public function has($key)
    {
        return array_key_exists($this->transformKey($key), $this->data);
    }

    public function remove($key)
    {
        unset($this->data[$this->transformKey($key)]);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
