<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerRestartCommand
 *
 * @package blink\console
 */
class ServerRestartCommand extends BaseServer
{
    public $name = 'server:restart';
    public $description = 'Restart a blink server';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handleRestart();
    }
}
