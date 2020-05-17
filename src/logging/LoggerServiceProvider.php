<?php

declare(strict_types=1);

namespace blink\logging;

use blink\injector\object\ObjectDefinition;
use blink\kernel\ServiceProvider;
use Psr\Log\LogLevel;

/**
 * Class LoggerServiceProvider
 *
 * @package blink\logging
 */
class LoggerServiceProvider extends ServiceProvider
{
    public function register($kernel): void
    {
        $kernel->define('logger.name')->required();
        $kernel->define('logger.log_file')->required();
        $kernel->define('logger.log_level')->default(LogLevel::INFO);

        $kernel->getContainer()->extend(Logger::class, function (ObjectDefinition $logger) {
            $logger->haveProperty('name')->referenceTo('logger.name');
            $logger->haveProperty('logFile')->referenceTo('logger.log_file');
            $logger->haveProperty('logLevel')->referenceTo('logger.log_level');
        });
    }
}
