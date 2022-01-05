<?php

namespace blink\log;

use Monolog\Formatter\LineFormatter;
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

    public $dateFormat = 'Y-m-d H:i:s.u';

    public $allowLineBreaks = false;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new StreamHandler($this->stream, $this->level, true, null, true);
            $formatter = new LineFormatter(null, $this->dateFormat, $this->allowLineBreaks);
            $this->handler->setFormatter($formatter);
        }
        return $this->handler;
    }
}
