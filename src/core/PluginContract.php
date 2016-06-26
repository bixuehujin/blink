<?php

namespace blink\core;

/**
 * Interface PluginContract
 *
 * @package blink\core
 * @since 0.3
 */
interface PluginContract
{
    /**
     * Bootstrap the plugin.
     *
     * This method will be called at the application bootstrapping stage.
     *
     * @param Application $app
     */
    public function bootstrap(Application $app);
}
