<?php

declare(strict_types=1);

namespace blink\rest\middleware;

use blink\http\Response;
use blink\support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ResponseNormalizer
 *
 * @package blink\rest\middleware
 */
class ResponseNormalizer implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof Response) {
            $content = is_string($response->data) ? $response->data : Json::encode($response->data);
            if (!is_string($response->data) && !$response->headers->has('Content-Type')) {
                $response->headers->set('Content-Type', 'application/json');
            }
            $response->getBody()->rewind();
            $response->getBody()->write($content);
        }

        return $response;
    }
}
