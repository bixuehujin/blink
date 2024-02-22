<?php

namespace blink\console;

use blink\console\Command;
use blink\core\InvalidValueException;
use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class BaseServerCommand
 *
 * @package blink\console
 */
class BaseServer extends Command implements ContainerAware
{
    use ContainerAwareTrait;

    protected function loadEnvFile(InputInterface $input)
    {
        $file = $input->getOption('env-file');

        if ($file) {
            (new Dotenv())->load($file);
        }
    }

    protected function getServerDefinition()
    {
        $configFile = $this->getContainer()->get('server.config_file');
        return require $configFile;
    }

    protected function getPidFile()
    {
        $server = $this->getServerDefinition();

        return !empty($server['pidFile']) ? $server['pidFile'] : $this->blink->runtime . '/server.pid';
    }

    protected function handleServe($liveReload = false)
    {
        $server             = $this->getServerDefinition();
        $server['asDaemon'] = 0;

        if ($liveReload) {
            $server['maxRequests'] = 1;
            $server['numWorkers']  = 1;
        }

        return $this->getContainer()->make2($server)->run();
    }

    protected function handleStart()
    {
        $server = $this->getServerDefinition();

        $pidFile = $this->getPidFile();

        if (file_exists($pidFile)) {
            throw new InvalidValueException('The pidfile exists, it seems the server is already started');
        }
        $server['asDaemon'] = 1;
        $server['pidFile']  = $pidFile;

        return $this->getContainer()->make2($server)->run();
    }

    protected function handleRestart()
    {
        $this->handleStop();

        return $this->handleStart();
    }

    protected function handleReload()
    {
        $pidFile = $this->getPidFile();

        unset($server);

        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 10)) {
            return 0;
        }

        return 1;
    }

    protected function handleStop()
    {
        $pidFile = $this->getPidFile();

        unset($server);

        if (file_exists($pidFile) && posix_kill(file_get_contents($pidFile), 15)) {
            do {
                usleep(100000);
            } while (file_exists($pidFile));
            return 0;
        }

        return 1;
    }
}
