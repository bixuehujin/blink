<?php

declare(strict_types=1);

namespace blink\logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Monolog\Logger as MonoLogger;
use Psr\Log\LogLevel;

/**
 * Class Logger
 *
 * @package blink\logging
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    //<<Inject('logger.name')>>
    public string $name;

    //<<Inject('logger.log_file')>>
    public string $logFile;

    //<<Inject('logger.log_level')>>
    public string $logLevel = LogLevel::INFO;

    protected ?MonoLogger $monoLogger = null;

    /**
     * @param string $name
     * @return Logger
     */
    public function withName(string $name): Logger
    {
        $new             = clone $this;
        $new->name       = $this->name . '.' . $name;
        $new->monoLogger = null;

        return $new;
    }

    public function log($level, $message, array $context = [])
    {
        $this->getMonoLogger()->log($level, $message, $context);
    }

    protected function getHandlers(): array
    {
        $handler = new StreamHandler($this->logFile, $this->logLevel, true, null, true);
        $handler->setFormatter(new LineFormatter(null, null, true));

        return [$handler];
    }

    protected function getMonoLogger(): MonoLogger
    {
        if (!$this->monoLogger) {
            $this->monoLogger = new MonoLogger($this->name, $this->getHandlers());
        }
        return $this->monoLogger;
    }
}

