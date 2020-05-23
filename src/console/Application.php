<?php

declare(strict_types=1);

namespace blink\console;

use blink\console\events\CommandRegistering;
use blink\eventbus\EventBus;
use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use Symfony\Component\Console\Application as Runner;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class Application
 *
 * @package blink\console
 */
class Application implements ContainerAware
{
    use ContainerAwareTrait;

    protected Runner $runner;

    public function __construct()
    {
        $this->runner = new Runner('blink cli');

        if ($file = getenv('ENV_FILE')) {
            (new Dotenv())->load($file);
        }
    }

    public function registerCommand(string $command)
    {
        $command = $this->getContainer()->get($command);

        $this->runner->add($command);
    }

    public function run()
    {
        $this->container->get(EventBus::class)
            ->dispatch(new CommandRegistering($this));

        return $this->runner->run(
            new ArgvInput(),
            new ConsoleOutput()
        );
    }
}
