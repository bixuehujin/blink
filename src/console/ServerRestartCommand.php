<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ServerRestartCommand
 *
 * @package blink\console
 */
class ServerRestartCommand extends BaseServer
{
    public $name = 'server:restart';
    public $description = 'Restart a blink server';

    protected function configure()
    {
        $this->addOption('env-file', null, InputOption::VALUE_REQUIRED, 'The env file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadEnvFile($input);

        return $this->handleRestart();
    }
}
