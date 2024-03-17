<?php

namespace blink\server;

use blink\core\InvalidConfigException;
use blink\eventbus\EventBus;
use blink\http\Cookie;
use blink\http\HeaderBag;
use blink\http\Request;
use blink\http\Response;
use blink\http\Stream;
use blink\http\Uri;
use blink\kernel\Kernel;
use blink\server\events\WorkerStarted;

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
    public int $maxRequests = 10000;

    /**
     * The rate to trigger zend memory manager's gc procedure, useful to release memory to system. Valid values are 0-1.
     *
     * @var float
     */
    public float $memoryGcRate = 0;

    /**
     * The max package length in bytes for swoole, which is default to 2M (1024 * 1024 * 2). Please refer
     * http://wiki.swoole.com/wiki/page/301.html for more detailed information.
     *
     * @var int|null
     */
    public ?int $maxPackageLength = null;

    /**
     * The output buffer size, defaults to 2M, see http://wiki.swoole.com/wiki/page/440.html
     *
     * @var int
     */
    public int $outputBufferSize = 2097152;

    /**
     * The max header size should be reseved for sending headers, used together with Chunked Encoding.
     *
     * @var int
     */
    public int $maxHeaderSize = 4096;

    /**
     * The number of workers should be started to serve requests.
     *
     * @var int|null
     */
    public ?int $numWorkers = null;

    /**
     * Detach the server process and run as daemon.
     *
     * @var bool
     */
    public bool $asDaemon = false;

    /**
     * The dispatch mode for swoole, defaults to 3 for http server.
     *
     * @var int
     * @see https://wiki.swoole.com/wiki/page/277.html
     */
    public int $dispatchMode = 3;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public string $logFile = '';

    public function init()
    {
        if (!extension_loaded('swoole')) {
            throw new \RuntimeException('The Swoole extension is required to run blink in SwServer.');
        }
        
        if ($this->maxHeaderSize >= $this->outputBufferSize) {
            throw new InvalidConfigException('The outputBufferSize config should be larger than maxHeaderSize.');
        }
    }

    protected function normalizedConfig()
    {
        $config = [];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;
        $config['dispatch_mode'] = $this->dispatchMode;

        if ($this->numWorkers) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ($this->logFile) {
            $config['log_file'] = $this->logFile;
        }

        if ($this->maxPackageLength) {
            $config['package_max_length'] = $this->maxPackageLength;
        }

        $config['buffer_output_size'] = $this->outputBufferSize;

        return $config;
    }

    protected function createServer()
    {
        $server = new \Swoole\Http\Server($this->host, $this->port);

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

        $this->getEventBus()->dispatch(new WorkerStarted());

        $router = $this->getRouter();
        
        $router->mountRoutes();

        $this->initContaier();
    }
    
    protected function setProcessTitle($title)
    {
        if (@cli_set_process_title($title) !== false) {
            return;
        }

        if (PHP_OS !== 'Darwin') {
            $error = error_get_last();
            if ($error) {
                trigger_error($error['message'], E_USER_WARNING);
            }
        } elseif (extension_loaded('proctitle')) {
            setproctitle($title);
        }
    }

    public function onWorkerStop()
    {
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

    protected function resolveSchema(HeaderBag $headers, $default)
    {
        if ($headers->first('x-forwarded-proto') === 'https'
            || (int)$headers->first('x-forwarded-port') === 443) {
            return 'https';
        }

        return $default;
    }

    public function createRequest($request)
    {
        $protocolParts = explode('/', $request->server['server_protocol']);
        $hostParts = explode(':', $request->header['host'] ?? $this->host . ':' . $this->port);
        $headers = new HeaderBag($request->header);

        $uriConfig = [
            'scheme' => $this->resolveSchema($headers, strtolower($protocolParts[0])),
            'query' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            'path' => $request->server['request_uri'],
            'host' => $hostParts[0],
        ];

        if (isset($hostParts[1])) {
            $uriConfig['port'] = $hostParts[1];
        }

        $body = new Stream('php://memory', 'w+');
        $body->write($request->rawContent());

        $config = [
            'uri' => new Uri('', $uriConfig),
            'protocol' => $protocolParts[1],
            'method' => $request->server['request_method'],
            'headers' => $headers,
            'cookies' => isset($request->cookie) ? $request->cookie : [],
            'body' => $body,
            'serverParams' => [
                'remote_addr' => $request->server['remote_addr'] ?: '127.0.0.1',
            ]
        ];

        if (!empty($request->files)) {
            $config['files'] = $this->normalizeFiles($request->files);
        }

        return new ($this->getRequestClass())($config);
    }

    public function onRequest($request, $response)
    {
        $container = $this->getContainer();
        $request = $this->createRequest($request);

        $res = $this->handleRequest($request);

        $content = (string)$res->getBody();

        foreach ($res->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        /** @var Cookie $cookie */
        foreach ($res->getCookies() as $cookie) {
            $response->cookie($cookie->name, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }

        $this->respond($response, $res->getStatusCode(), $content);

        $this->gc();
    }

    protected function respond($response, $status, $content)
    {
        $response->status($status);
        
        $maxWriteSize = $this->outputBufferSize  - $this->maxHeaderSize;

        if (strlen($content) <= $maxWriteSize) {
            $response->end($content);
        } else {
            $response->header('Transfer-Encoding', 'chunked');

            $segments = ceil(strlen($content) / $maxWriteSize);
            
            for ($i = 0; $i < $segments; $i ++) {
                $start = $i * $maxWriteSize;
                $buffer = substr($content, $start, $maxWriteSize);
                $n = $response->write($buffer);
            }

            $response->end();
        }
    }

    protected function gc()
    {
        if ($this->memoryGcRate > 0 && $this->maxRequests > 1) {
            $shouldGc = (mt_rand(0, $this->maxRequests) / $this->maxRequests) < $this->memoryGcRate;
            if ($shouldGc && function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }
        }
    }

    public function run()
    {
        $server = $this->createServer();
        $server->start();
    }
}
