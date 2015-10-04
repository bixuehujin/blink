<?php

namespace blink\core\console;

use blink\core\Configurable;
use blink\core\ObjectTrait;
use Symfony\Component\Console\Application as SymfonyConsole;

/**
 * Class Application
 *
 * @package blink\core\console
 */
class Application extends SymfonyConsole implements Configurable
{
    use ObjectTrait;

    public $name = 'UNKNOWN';
    public $version = 'UNKNOWN';
    public $blink;

    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        parent::__construct($this->name, $this->version);

        $this->init();
    }
}
