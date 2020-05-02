<?php

declare(strict_types=1);

namespace blink\server;

use blink\console\ShellCommand;
use blink\kernel\ServiceProvider;
use blink\console\Application;
use blink\console\ServerReloadCommand;
use blink\console\ServerRestartCommand;
use blink\console\ServerServeCommand;
use blink\console\ServerStartCommand;
use blink\console\ServerStopCommand;

/**
 * Class ServerProvider
 *
 * @package blink\kernel
 */
class ServerProvider extends ServiceProvider
{
    /**
     * @param Application|Server $kernel
     * @return void
     */
    public function register($kernel): void
    {
        $kernel->define('server.config_file')->required();
        $kernel->define('server.host')->default('0.0.0.0');
        $kernel->define('server.port')->default(7788);

        if ($kernel instanceof Application) {
            $kernel->registerCommand(ServerStartCommand::class);
            $kernel->registerCommand(ServerServeCommand::class);
            $kernel->registerCommand(ServerStopCommand::class);
            $kernel->registerCommand(ServerRestartCommand::class);
            $kernel->registerCommand(ServerReloadCommand::class);
            $kernel->registerCommand(ShellCommand::class);
        }
    }
}
