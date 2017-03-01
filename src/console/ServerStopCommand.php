<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerStopCommand
 *
 * @package blink\console
 */
class ServerStopCommand extends BaseServer
{
    public $name = 'server:stop';
    public $description = 'Stop the running blink server';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handleStop();
    }
}
