<?php

declare(strict_types=1);

namespace blink\routing\middleware;

use blink\http\Response;
use Throwable;
use blink\routing\exceptions\MethodNotAllowedException;
use blink\routing\exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ErrorCatcher
 *
 * @package blink\routing\middleware
 */
class ErrorCatcher implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $resp = new Response();
            $resp->data = $e;
            if ($e instanceof RouteNotFoundException) {
                $resp->status(404);
            } elseif ($e instanceof MethodNotAllowedException) {
                $resp->status(405);
            } else {
                $resp->status(500);
            }

            return $resp;
        }
    }
}
