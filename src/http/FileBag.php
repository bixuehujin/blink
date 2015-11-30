<?php

namespace blink\http;

use Countable;
use ArrayIterator;
use blink\core\Object;
use IteratorAggregate;

/**
 * FileBag represents a set of uploaded files.
 *
 * @package blink\http
 */
class FileBag extends Object implements IteratorAggregate, Countable
{
    private $files;

    public function __construct(array $files = [], $config = [])
    {
        $this->replace($files);

        parent::__construct($config);
    }

    public function all()
    {
        return $this->files;
    }

    public function keys()
    {
        return array_keys($this->files);
    }

    public function replace($files)
    {
        foreach ($files as $key => $file) {
            $this->set($key, $file);
        }
    }

    public function set($key, $value)
    {
        if (!$value instanceof File) {
            $value = new File($value);
        }

        $this->files[$key] = $value;
    }

    /**
     * Returns files by its name.
     *
     * @param $name
     * @return File[]
     */
    public function get($name)
    {
        if (isset($this->files[$name])) {
            return [$this->files[$name]];
        }

        $results = [];
        foreach ($this->files as $key => $file) {
            if (strpos($key, "{$name}[") === 0) {
                $results[] = $file;
            }
        }

        return $results;
    }

    /**
     * Returns the first file by its name.
     *
     * @param $name
     * @return File|null
     */
    public function first($name)
    {
        if (isset($this->files[$name])) {
            return $this->files[$name];
        }

        foreach ($this->files as $key => $file) {
            if (strpos($key, "{$name}[") === 0) {
                return $file;
            }
        }
    }

    public function count()
    {
        return count($this->files);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->files);
    }
}
