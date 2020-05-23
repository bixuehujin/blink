<?php

declare(strict_types=1);

namespace blink\server;

use blink\console\events\CommandRegistering;
use blink\console\ShellCommand;
use blink\eventbus\EventBus;
use blink\injector\config\ConfigContainer;
use blink\injector\Container;
use blink\kernel\events\AppInitializing;
use blink\injector\ServiceProvider;
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
    public function registerCommands(CommandRegistering $event)
    {
        $app = $event->app;

        $app->registerCommand(ServerStartCommand::class);
        $app->registerCommand(ServerServeCommand::class);
        $app->registerCommand(ServerStopCommand::class);
        $app->registerCommand(ServerRestartCommand::class);
        $app->registerCommand(ServerReloadCommand::class);
        $app->registerCommand(ShellCommand::class);
    }

    public function register(Container $container): void
    {
        $store = $container->get(ConfigContainer::class);
        $store->define('server.config_file')->required();
        $store->define('server.host')->default('0.0.0.0');
        $store->define('server.port')->default(7788);

        $container
            ->get(EventBus::class)
            ->attach(
                CommandRegistering::class,
                [$this, 'registerCommands']
            );
    }
}
