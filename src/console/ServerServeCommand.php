<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerServeCommand
 *
 * @package blink\console
 */
class ServerServeCommand extends BaseServer
{
    public $name = 'server:serve';
    public $description = 'Start a blink server to serve';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->handleServe();
    }
}
