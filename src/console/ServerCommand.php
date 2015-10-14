<?php

namespace blink\console;

use blink\core\console\Command;
use blink\core\InvalidParamException;
use blink\core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ServerCommand
 *
 * @package blink\console
 */
class ServerCommand extends Command
{
    public $name = 'server';
    public $description = 'Blink server management';

    protected function configure()
    {
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: serve, start, restart or stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['serve', 'start', 'restart', 'stop'])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);

    }

    protected function handleServe()
    {
        $server = require $this->blink->root . '/src/config/server.php';
        $server['asDaemon'] = 0;

        return make($server)->run();
    }

    protected function handleStart()
    {
        $pidFile = $this->blink->root . '/runtime/server.pid';

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }

        $server = require $this->blink->root . '/src/config/server.php';
        $server['asDaemon'] = 1;
        $server['pidFile'] = $this->blink->root . '/runtime/server.pid';

        return make($server)->run();
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleStop()
    {
        $pidFile = $this->blink->root . '/runtime/server.pid';
        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
            do {
                usleep(100000);
            } while(file_exists($pidFile));
            return 0;
        }

        return 1;
    }
}
