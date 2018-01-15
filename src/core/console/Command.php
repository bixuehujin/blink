<?php

namespace blink\core\console;

use blink\core\Configurable;
use blink\core\ObjectTrait;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 *
 * @package blink\core
 */
class Command extends SymfonyCommand implements Configurable
{
    use ObjectTrait;

    /**
     * The blink app instance.
     *
     * @var \blink\core\Application
     */
    public $blink;

    /**
     * The name of the command.
     *
     * @var string
     */
    public $name;

    /**
     * The description of the command.
     *
     * @var string
     */
    public $description;

    /**
     * Whether or not bootstrap the application automatically, defaults to true.
     *
     * @var bool
     */
    public $bootstrap = true;

    /**
     * @var InputInterface
     */
    public $input;

    /**
     * @var OutputInterface
     */
    public $output;

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        parent::__construct($this->name);

        $this->setDescription($this->description);

        $this->init();
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        if ($this->bootstrap) {
            $this->blink->bootstrapIfNeeded();
        }

        $this->input = $input;
        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @return void
     */
    public function info($string)
    {
        $this->output->writeln("<info>$string</info>");
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @return void
     */
    public function line($string)
    {
        $this->output->writeln($string);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @return void
     */
    public function comment($string)
    {
        $this->output->writeln("<comment>$string</comment>");
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @return void
     */
    public function question($string)
    {
        $this->output->writeln("<question>$string</question>");
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @return void
     */
    public function error($string)
    {
        $this->output->writeln("<error>$string</error>");
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @return void
     */
    public function warn($string)
    {
        $style = new OutputFormatterStyle('yellow');

        $this->output->getFormatter()->setStyle('warning', $style);

        $this->output->writeln("<warning>$string</warning>");
    }
}
