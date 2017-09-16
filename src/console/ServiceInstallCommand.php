<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServiceInstallCommand
 *
 * @package blink\console
 */
class ServiceInstallCommand extends BaseService
{
    public $name = 'service:install';
    public $description = 'Install {serviceName} as a system service';
    public $bin = 'blink';

    protected function configure()
    {
        $this->addOption('php', null, InputOption::VALUE_REQUIRED, 'Specify a custom php executable');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureRootPrivilege();
        $this->ensurePkgConfigInstalled();

        $unitFile = $this->getSystemUnitDir() . '/' . $this->serviceName . '.service';

        file_put_contents($unitFile, $a = $this->getServiceConfig($input->getOption('php')));

        $envFile = '/etc/default/' . $this->serviceName;
        if (!file_exists($envFile)) {
            file_put_contents($envFile, $this->getEnvConfig());
        }

        system('systemctl enable ' . $this->serviceName);

        $this->info('System service installed successfully.');
    }

    protected function getServiceConfig($php)
    {
        $template = <<<'CONFIG'
[Unit]
Description={service_description}

[Service]
EnvironmentFile=-/etc/default/{service_name}
Type=forking
ExecStart={php}{bin_file} server:start
ExecReload={php}{bin_file} server:reload
ExecStop={php}{bin_file} server:stop
PIDFile={pid_file}
KillMode=process
Restart=on-failure

[Install]
WantedBy=multi-user.target
CONFIG;

        return strtr($template, [
            '{php}' => $php ? $php . ' ' : '',
            '{bin_file}' => $this->blink->root . '/' . $this->bin,
            '{pid_file}' => $this->getPidFile(),
            '{service_name}' =>  $this->serviceName,
            '{service_description}' => 'The ' . $this->serviceName . ' service',
        ]);
    }

    protected function getEnvConfig()
    {
        return file_get_contents($this->blink->root . '/env.example');
    }
}
