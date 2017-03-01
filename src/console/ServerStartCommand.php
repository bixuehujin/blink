<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerStartCommand
 *
 * @package blink\console
 */
class ServerStartCommand extends BaseServer
{
    public $name = 'server:start';
    public $description = 'Start a blink server as daemon';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handleStart();
    }
}
