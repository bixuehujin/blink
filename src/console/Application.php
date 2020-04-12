<?php

declare(strict_types=1);

namespace blink\console;

use blink\kernel\Kernel;
use Symfony\Component\Console\Application as Runner;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class Application
 *
 * @package blink\console
 */
class Application extends Kernel
{
    protected Runner $runner;

    public function __construct()
    {
        $this->runner = new Runner('blink cli');

        if ($file = getenv('ENV_FILE')) {
            (new Dotenv())->load($file);
        }

        parent::__construct();
    }

    public function registerCommand($command)
    {
        $command = $this->container->get($command);
        $command->container = $this->container;

        $this->runner->add($command);
    }

    public function run()
    {
        return $this->runner->run(
            new ArgvInput(),
            new ConsoleOutput()
        );
    }
}
