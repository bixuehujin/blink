<?php

namespace blink\console;

use Psy\Shell;
use Psy\Configuration;
use blink\console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShellCommand
 *
 * @package blink\console
 * @since 0.3
 */
class ShellCommand extends Command
{
    public string $name = 'shell';
    public string $description = 'Interact with your application';
    public array $casters = [];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Configuration();
        $config->getPresenter()->addCasters($this->casters);

        $shell = new Shell($config);

        return $shell->run();
    }
}
