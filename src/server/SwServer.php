<?php

namespace blink\server;

use blink\http\Request;

/**
 * A Swoole based server implementation.
 *
 * @package blink\server
 */
class SwServer extends Server
{
    public $swConfig = [];

    private $defaultSwConfig = [
        'worker_num' => 2,
        'task_worker_num' => 2,
    ];

    private function createServer()
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

        $server->set($this->swConfig + $this->defaultSwConfig);

        return $server;
    }


    public function onServerStart($server)
    {
        //cli_set_process_title($this->name . ': master');
        if ($this->pidFile) {
            file_put_contents($this->pidFile, $server->master_pid);
        }
    }

    public function onManagerStart($server)
    {
        //cli_set_process_title($this->name . ': manager');
    }

    public function onServerStop()
    {
        if ($this->pidFile) {
            unlink($this->pidFile);
        }
    }

    public function onWorkerStart()
    {
        //cli_set_process_title($this->name . ': worker');
        $this->startApp();
    }

    public function onWorkerStop()
    {
        $this->stopApp();
    }

    public function onTask()
    {

    }

    public function onFinish()
    {

    }

    protected function prepareRequest($request)
    {
        $config = [
            'protocol' => $request->server['server_protocol'],
            'method' => $request->server['request_method'],
            'path' => $request->server['request_uri'],
            'headers' => $request->header,
            'params' => isset($request->get) ? $request->get : [],
            'content' => $request->rawcontent()
        ];

        return app()->makeRequest($config);
    }

    public function onRequest($request, $response)
    {
        $res = $this->handleRequest($this->prepareRequest($request));

        $content = $res->content();

        foreach ($res->headers->all() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach($values as $value) {
                $response->header($name, $value);
            }
        }
        $response->header('Content-Length', strlen($content));

        $response->status($res->statusCode);
        $response->end($content);
    }

    public function run()
    {
        $server = $this->createServer();
        $server->start();
    }
}
