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
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: serve, start, reload, restart or stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['serve', 'start', 'reload', 'restart', 'stop'])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);

    }

    protected function getServerDefinition()
    {
        if (isset($this->blink->server) && is_array($this->blink->server)) {
            return $this->blink->server;
        } else {
            return require $this->blink->root . '/src/config/server.php';
        }
    }

    protected function handleServe()
    {
        $server = $this->getServerDefinition();
        $server['asDaemon'] = 0;

        return make($server)->run();
    }

    protected function handleStart()
    {
        $server = $this->getServerDefinition();

        $pidFile = !empty($server['pidFile']) ? $server['pidFile'] : $this->blink->runtime . '/server.pid';

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }
        $server['asDaemon'] = 1;
        $server['pidFile'] = $pidFile;

        return make($server)->run();
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleReload()
    {
        $server = $this->getServerDefinition();

        $pidFile = !empty($server['pidFile']) ? $server['pidFile'] : $this->blink->runtime . '/server.pid';

        unset($server);

        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 10)) {
            return 0;
        }

        return 1;
    }

    protected function handleStop()
    {
        $server = $this->getServerDefinition();

        $pidFile = !empty($server['pidFile']) ? $server['pidFile'] : $this->blink->runtime . '/server.pid';

        unset($server);

        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
            do {
                usleep(100000);
            } while(file_exists($pidFile));
            return 0;
        }

        return 1;
    }
}
