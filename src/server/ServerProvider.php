<?php

declare(strict_types=1);

namespace blink\server;

use blink\console\events\CommandRegistering;
use blink\console\ShellCommand;
use blink\eventbus\EventBus;
use blink\di\config\ConfigContainer;
use blink\di\Container;
use blink\di\ServiceProvider;
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
    protected EventBus $eventBus;

    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

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
        $store->define('server.request_class')->required();
        $store->define('server.request_class')->required();

        $this->eventBus->attach(
            CommandRegistering::class,
            [$this, 'registerCommands']
        );
    }
}
