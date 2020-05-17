<?php

declare(strict_types=1);

namespace blink\server;

use blink\console\ShellCommand;
use blink\eventbus\EventBus;
use blink\kernel\events\AppInitializing;
use blink\kernel\Kernel;
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
    public function registerCommands(AppInitializing $event)
    {
        $container = $event->kernel->getContainer();
        $app = $container->get(Application::class);

        $app->registerCommand(ServerStartCommand::class);
        $app->registerCommand(ServerServeCommand::class);
        $app->registerCommand(ServerStopCommand::class);
        $app->registerCommand(ServerRestartCommand::class);
        $app->registerCommand(ServerReloadCommand::class);
        $app->registerCommand(ShellCommand::class);
    }

    /**
     * @param Kernel $kernel
     * @return void
     */
    public function register($kernel): void
    {
        $kernel->define('server.config_file')->required();
        $kernel->define('server.host')->default('0.0.0.0');
        $kernel->define('server.port')->default(7788);

        $kernel->getContainer()->get(EventBus::class)->attach(AppInitializing::class, [$this, 'registerCommands']);
    }
}
