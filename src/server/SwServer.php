<?php

namespace blink\server;

/**
 * A Swoole based server implementation.
 *
 * @package blink\server
 */
class SwServer extends Server
{
    /**
     * The number of requests each process should execute before respawning, This can be useful to work around
     * with possible memory leaks.
     *
     * @var int
     */
    public $maxRequests = 10000;

    /**
     * The max package length in bytes for swoole, which is default to 2M (1024 * 1024 * 2). Please refer
     * http://wiki.swoole.com/wiki/page/301.html for more detailed information.
     *
     * @var int
     */
    public $maxPackageLength;

    /**
     * The output buffer size, see http://wiki.swoole.com/wiki/page/440.html
     *
     * @var int
     */
    public $outputBufferSize;

    /**
     * The number of workers should be started to serve requests.
     *
     * @var int
     */
    public $numWorkers;

    /**
     * Detach the server process and run as daemon.
     *
     * @var bool
     */
    public $asDaemon = false;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public $logFile;


    public function init()
    {
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('The Swoole extension is required to run blink in SwServer.');
        }
    }

    protected function normalizedConfig()
    {
        $config = [];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;

        if ($this->numWorkers) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ($this->logFile) {
            $config['log_file'] = $this->logFile;
        }

        if ($this->maxPackageLength) {
            $config['package_max_length'] = $this->maxPackageLength;
        }

        if ($this->outputBufferSize) {
            $config['buffer_output_size'] = $this->outputBufferSize;
        }

        return $config;
    }

    protected function createServer()
    {
        $server = new \swoole_http_server($this->host, $this->port);

        $server->on('start', [$this, 'onServerStart']);
        $server->on('shutdown', [$this, 'onServerStop']);

        $server->on('managerStart', [$this, 'onManagerStart']);

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$this, 'onWorkerStop']);

        $server->on('request', [$this, 'onRequest']);

        if (method_exists($this, 'onOpen')) {
            $server->on('open', [$this, 'onOpen']);
        }
        if (method_exists($this, 'onClose')) {
            $server->on('close', [$this, 'onClose']);
        }

        if (method_exists($this, 'onWsHandshake')) {
            $server->on('handshake', [$this, 'onWsHandshake']);
        }
        if (method_exists($this, 'onWsMessage')) {
            $server->on('message', [$this, 'onWsMessage']);
        }

        if (method_exists($this, 'onTask')) {
            $server->on('task', [$this, 'onTask']);
        }
        if (method_exists($this, 'onFinish')) {
            $server->on('finish', [$this, 'onFinish']);
        }

        $server->set($this->normalizedConfig());

        return $server;
    }


    public function onServerStart($server)
    {
        $this->setProcessTitle($this->name . ': master');

        if ($this->pidFile) {
            file_put_contents($this->pidFile, $server->master_pid);
        }
    }

    public function onManagerStart($server)
    {
        $this->setProcessTitle($this->name . ': manager');
    }

    public function onServerStop()
    {
        if ($this->pidFile) {
            unlink($this->pidFile);
        }
    }

    public function onWorkerStart()
    {
        $this->setProcessTitle($this->name . ': worker');

        $this->startApp();
    }
    
    protected function setProcessTitle($title)
    {
        if (@cli_set_process_title($title) !== false) {
            return;
        }

        if (PHP_OS !== 'Darwin') {
            $error = error_get_last();
            trigger_error($error['message'], E_USER_WARNING);
        } elseif (extension_loaded('proctitle')) {
            setproctitle($title);
        }
    }

    public function onWorkerStop()
    {
        $this->stopApp();
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
    }

    public function onFinish($server, $taskId, $data)
    {
    }

    protected function normalizeFiles($files)
    {
        foreach ($files as $name => &$file) {
            $file['tmpName'] = $file['tmp_name'];
            unset($file['tmp_name']);
        }

        return $files;
    }

    protected function prepareRequest($request)
    {
        $config = [
            'protocol' => $request->server['server_protocol'],
            'method' => $request->server['request_method'],
            'path' => $request->server['request_uri'],
            'headers' => $request->header,
            'params' => isset($request->get) ? $request->get : [],
            'cookies' => isset($request->cookie) ? $request->cookie : [],
            'content' => $request->rawContent()
        ];

        if (!empty($request->files)) {
            $config['files'] = $this->normalizeFiles($request->files);
        }

        return app()->makeRequest($config);
    }

    public function onRequest($request, $response)
    {
        $res = $this->handleRequest($this->prepareRequest($request));

        $content = $res->content();

        foreach ($res->headers->all() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }
        $response->header('Content-Length', strlen($content));
        foreach ($res->cookies as $cookie) {
            $response->header('Set-Cookie', $cookie->toString());
        }

        $response->status($res->statusCode);
        $response->end($content);
    }

    public function run()
    {
        $server = $this->createServer();
        $server->start();
    }
}
