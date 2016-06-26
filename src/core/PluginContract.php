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
     * Install the plugin.
     *
     * This method will be called at the application bootstrapping stage.
     *
     * @param Application $app
     */
    public function install(Application $app);
}
