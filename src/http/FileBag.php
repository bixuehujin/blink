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
}
