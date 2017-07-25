<?php
/**
 * Created by PhpStorm.
 * User: hujin
 * Date: 15-10-2
 * Time: 下午5:45
 */

namespace blink\log;

use blink\core\Object;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;
use Psr\Log\LogLevel;

abstract class Target extends Object implements HandlerInterface
{
    /**
     * Whether to enable this log target, Defaults to true.
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * List of message channels that this target is interested in. Defaults to empty, meaning all channels.
     *
     * @var array
     */
    public $only = [];

    /**
     * List of message channels that this target is NOT interested in. Default to empty, meaning no uninteresting
     * messages. If this property is not empty, then any channels listed here will be excluded from [[only]].
     *
     * @var array
     */
    public $except = [];

    /**
     * The minimum logging level at which this target will be enabled.
     *
     * @var array
     */
    public $level = LogLevel::NOTICE;

    /**
     * @return HandlerInterface
     */
    abstract public function getUnderlyingHandler();

    public function isHandling(array $message)
    {
        return $this->enabled && $this->getUnderlyingHandler()->isHandling($message);
    }

    public function handle(array $message)
    {
        $this->getUnderlyingHandler()->handle($message);
    }

    public function handleBatch(array $messages)
    {
        $this->getUnderlyingHandler()->handleBatch($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        $this->getUnderlyingHandler()->pushProcessor($callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        return $this->getUnderlyingHandler()->popProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->getUnderlyingHandler()->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->getUnderlyingHandler()->getFormatter();
    }
}
