<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerReloadCommand
 *
 * @package blink\console
 */
class ServerReloadCommand extends BaseServer
{
    public $name = 'server:reload';
    public $description = 'Reload the running server';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handleReload();
    }
}
