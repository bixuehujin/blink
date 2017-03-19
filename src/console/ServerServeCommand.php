<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerServeCommand
 *
 * @package blink\console
 */
class ServerServeCommand extends BaseServer
{
    public $name = 'server:serve';
    public $description = 'Start a blink server in foreground';

    protected function configure()
    {
        $this->addOption('cli', null, InputOption::VALUE_NONE, "Serve requests using php's built-in web server");
        $this->addOption('live-reload', null, InputOption::VALUE_NONE, "Reload server on every requests, useful on development environment");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cliMode = $input->getOption('cli');

        if (!$cliMode && !extension_loaded('swoole')) {
            $this->error('The Swoole extension is not installed, the PHP\'s built-in web server will be used');
            $cliMode = true;
        }

        if ($cliMode) {
            return $this->handleCliServe();
        } else {
            return $this->handleServe($input->getOption('live-reload'));
        }
    }

    protected function handleCliServe()
    {
        $server = $this->getServerDefinition();
        $port = isset($server['port']) ? $server['port'] : 7788;
        $args = ['-S', '0.0.0.0:' . $port, __DIR__ . '/../support/server.php'];

        $path = shell_exec('which php');

        return pcntl_exec(trim($path), $args);
    }
}
