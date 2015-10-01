<?php

namespace blink\support;


use ArrayIterator;


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

    public function count()
    {
        return count($this->data);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
