<?php

namespace blink\http;

use Countable;
use blink\core\Object;
use IteratorAggregate;
use blink\support\BagTrait;

/**
 * FileBag represents a set of uploaded files.
 *
 * @package blink\http
 */
class FileBag extends Object implements IteratorAggregate, Countable
{
    use BagTrait;

    public function __construct(array $files = [], $config = [])
    {
        $this->replace($files);

        parent::__construct($config);
    }

    /**
     * Returns the first file by given key.
     *
     * @param mixed $key
     * @return File|File[]|null
     */
    public function first($key = null)
    {
        if ($key !== null) {
            if ($files = $this->get($key)) {
                return reset($files);
            } else {
                return;
            }
        }

        $ret = [];

        foreach ($this->all() as $key => $files) {
            $ret[] = reset($files);
        }

        return $ret;
    }
}
