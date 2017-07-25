<?php

namespace blink\console;

use blink\core\InvalidParamException;
use blink\core\InvalidValueException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerCommand
 *
 * @package blink\console
 */
class ServerCommand extends BaseServer
{
    public $name = 'server';
    public $description = 'Blink server management (deprecated)';

    protected function configure()
    {
        $this->addArgument('operation', InputArgument::REQUIRED, 'the operation: serve, start, reload, restart or stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');

        if (!in_array($operation, ['serve', 'start', 'reload', 'restart', 'stop'], true)) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);
    }
}
