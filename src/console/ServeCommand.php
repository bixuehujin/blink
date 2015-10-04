<?php

namespace blink\console;

use blink\core\console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ServeCommand
 *
 * @package blink\console
 */
class ServeCommand extends Command
{
    public $name = 'server:serve';
    public $description = 'Start http server and serve incoming requests';


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = require $this->blink->root . '/src/config/server.php';
        return make($server)->run();
    }
}
