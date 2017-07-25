<?php

namespace blink\console;

use blink\core\console\Command;
use blink\core\InvalidValueException;

/**
 * Class BaseServerCommand
 *
 * @package blink\console
 */
class BaseServer extends Command
{
    /**
     * We disabled app auto bootstrapping here to make lazy loading works.
     *
     * @var bool
     */
    public $bootstrap = false;

    protected function getServerDefinition()
    {
        if (isset($this->blink->server) && is_array($this->blink->server)) {
            return $this->blink->server;
        } else {
            return require $this->blink->root . '/src/config/server.php';
        }
    }

    protected function getPidFile()
    {
        $server = $this->getServerDefinition();

        return !empty($server['pidFile']) ? $server['pidFile'] : $this->blink->runtime . '/server.pid';
    }

    protected function handleServe($liveReload = false)
    {
        $server = $this->getServerDefinition();
        $server['asDaemon'] = 0;

        if ($liveReload) {
            $server['maxRequests'] = 1;
            $server['numWorkers'] = 1;
        }

        return make($server)->run();
    }

    protected function handleStart()
    {
        $server = $this->getServerDefinition();

        $pidFile = $this->getPidFile();

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
