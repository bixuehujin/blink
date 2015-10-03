<?php

namespace blink\log;

use blink\base\InvalidParamException;
use blink\base\Object;
use blink\Blink;
use blink\di\Instance;
use Monolog\Formatter\JsonFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Monolog\Logger as MonoLogger;

/**
 * Class Logger
 *
 * @package blink\log
 */
class Logger extends Object implements LoggerInterface
{
    use LoggerTrait;

    public $name = 'blink';
    public $targets = [];

    protected $monolog;

    protected $levelMap = [
        'emergency' => MonoLogger::EMERGENCY,
        'alert' => MonoLogger::ALERT,
        'critical' => MonoLogger::CRITICAL,
        'error' => MonoLogger::ERROR,
        'warning' => MonoLogger::WARNING,
        'notice' => MonoLogger::NOTICE,
        'info' => MonoLogger::INFO,
        'debug' => MonoLogger::DEBUG,
    ];

    public function init()
    {
        $this->monolog = new MonoLogger($this->name);

        foreach ($this->targets as &$target) {
            $target = make($target);
            $this->monolog->pushHandler($target);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->levelMap[$level])) {
            throw new InvalidParamException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys($this->levelMap)));
        }

        $this->monolog->addRecord($this->levelMap[$level], $message, $context);
    }
}
