<?php

namespace blink\server;

use blink\core\BaseObject;
use blink\di\ContainerAware;
use blink\di\ContainerAwareTrait;
use blink\eventbus\EventBus;
use blink\http\Request;
use blink\http\Response;
use blink\routing\Router;

/**
 * The base class for Application Server.
 *
 * @package blink\server
 */
abstract class Server extends BaseObject implements ContainerAware
{
    use ContainerAwareTrait;

    public string $host = '0.0.0.0';
    public int    $port = 7788;

    public string $name = 'blink-server';
    public string $pidFile = '';

    public function getRouter(): Router
    {
        return $this->getContainer()->get(Router::class);
    }
    
    public function getEventBus(): EventBus
    {
        return $this->getContainer()->get(EventBus::class);
    }

    public function initContaier(): void
    {
        $requestClass = $this->getRequestClass();
        if ($requestClass !== Request::class) {
            $this->container->alias($requestClass, Request::class); 
        }

        $responseClass = $this->getResponseClass();
        if ($responseClass !== Response::class) {
            $this->container->alias($responseClass, Response::class);
        }
    }
    
    public function handleRequest(Request $request): Response
    {
        $this->container->bind($request::class, $request);

        $response = $this->getRouter()->handle($request);

        $this->container->unset($request::class);
        $this->container->unset($response::class);

        return $response;
    }

    public function getRequestClass(): string 
    {
        return $this->container->get('server.request_class');     
    }
    
    public function getResponseClass(): string 
    {
        return $this->container->get('server.response_class');     
    }

    abstract public function run();
}
