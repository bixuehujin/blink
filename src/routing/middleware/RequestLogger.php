<?php

declare(strict_types=1);

namespace blink\routing\middleware;

use blink\http\Request;
use blink\http\Response;
use blink\logging\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RequestLogger
 *
 * @package blink\routing\middleware
 */
class RequestLogger implements MiddlewareInterface
{
    protected Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger->withName('request');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request, $handler);

        $message = sprintf("%s %s [%d]", $request->getMethod(), $request->getUri(), $response->getStatusCode());

        $this->logger->info($message, [
            'request'  => [
                'headers' => $request instanceof Request ? $request->headers->all() : $request->getHeaders(),
                'body'    => (string)$request->getBody(),
            ],
            'response' => [
                'headers' => $response instanceof Response ? $response->headers->all() : $response->getHeaders(),
                'body'    => (string)$response->getBody(),
            ],
        ]);

        return $response;
    }
}
