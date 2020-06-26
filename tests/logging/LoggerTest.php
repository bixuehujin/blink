<?php

declare(strict_types=1);

namespace blink\tests\logging;

use blink\core\Exception;
use blink\di\config\ConfigContainer;
use blink\di\Container;
use blink\logging\Logger;
use blink\logging\LoggerServiceProvider;
use blink\tests\TestCase;
use Psr\Log\LogLevel;

/**
 * Class LoggerTest
 *
 * @package blink\tests\logging
 */
class LoggerTest extends TestCase
{
    protected string $logFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->logFile = __DIR__ . '/test.log';
    }

    public function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        parent::tearDown();
    }

    protected function createLogger(string $name, string $logFile, string $level): Logger
    {
        $container = new Container();
        $container->add(new LoggerServiceProvider());

        $config = $container->get(ConfigContainer::class);
        $config->set('logger.name', $name);
        $config->set('logger.log_file', $logFile);
        $config->set('logger.log_level', $level);

        return $container->get(Logger::class);
    }

    public function testLogMessage()
    {
        $logger = $this->createLogger('blink', $this->logFile, LogLevel::WARNING);
        $this->assertEquals(LogLevel::WARNING, $logger->logLevel);

        $logger->alert('alert message');
        $logger->info('info message');

        $content = file_get_contents($this->logFile);

        $this->assertTrue(strpos($content, 'alert message') !== false);
        $this->assertFalse(strpos($content, 'info message') !== false);
    }

    public function testLogException()
    {
        $logger = $this->createLogger('blink', $this->logFile, LogLevel::WARNING);

        $logger->alert(new Exception('my exception'));

        $this->assertTrue(true);
    }
}
