<?php

namespace blink\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServiceUninstallCommand
 *
 * @package rethink\hrouter\console
 */
class ServiceUninstallCommand extends BaseService
{
    public $name = 'service:uninstall';
    public $description = 'Uninstall {serviceName} service from the system';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureRootPrivilege();
        $this->ensurePkgConfigInstalled();

        $unitFile = $this->getSystemUnitDir() . '/' . $this->serviceName . '.service';

        if (!file_exists($unitFile)) {
            return;
        }

        system('systemctl disable ' . $this->serviceName);

        unlink($unitFile);

        $this->info('System service uninstalled successfully.');
    }
}
