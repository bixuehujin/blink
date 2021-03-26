<?php

namespace blink\log;

use Monolog\Handler\StreamHandler;

/**
 * Class StreamTarget
 *
 * @package blink\log
 */
class StreamTarget extends Target
{
    /**
     * The stream to logging into.
     *
     * @var resource|string
     */
    public $stream;

    public $allowLineBreaks = false;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new StreamHandler($this->stream, $this->level, true, null, true);
            $this->handler->getFormatter()->allowInlineLineBreaks($this->allowLineBreaks);
        }
        return $this->handler;
    }
}
