<?php

namespace blink\console;

/**
 * Class BaseService
 *
 * @package blink\console
 */
class BaseService extends BaseServer
{
    public $serviceName;

    public function init()
    {
        $this->setDescription(strtr($this->description, ['{serviceName}' => $this->getServiceName()]));

        $this->serviceName = $this->getServiceName();
    }

    protected function getServiceName()
    {
        $serverConfig = $this->blink->root . '/src/config/server.php';

        $config = [];

        if (file_exists($serverConfig)) {
            $config = require $serverConfig;
        }

        return $config['name'] ?? $this->blink->name;
    }

    protected function whoami()
    {
        return posix_getpwuid(posix_geteuid())['name'];
    }

    protected function ensureRootPrivilege()
    {
        if ($this->whoami() !== 'root') {
            throw new \RuntimeException('Root privilege is required to install service');
        }
    }

    protected function ensurePkgConfigInstalled()
    {
        system('which pkg-config >/dev/null', $retval);

        if ($retval !== 0) {
            throw new \RuntimeException('It seems pkg-config not missing from your system, please install it first');
        }
    }

    protected function getSystemUnitDir()
    {
        ob_start();

        system('pkg-config systemd --variable=systemdsystemunitdir 2>&1', $retval);

        $output = ob_get_clean();

        if ($retval !== 0) {
            throw new \RuntimeException("Unable to get the directory of systemd unit files:\n" . $output);
        }

        return trim($output);
    }
}
