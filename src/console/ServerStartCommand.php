<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure()
    {
        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'The env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadEnvFile($input);

        return $this->handleStart();
    }
}
