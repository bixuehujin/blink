<?php

declare(strict_types=1);

namespace blink\routing\middleware;

use blink\core\ErrorHandler;
use blink\di\attributes\Inject;
use blink\http\Response;
use Throwable;
use blink\routing\exceptions\MethodNotAllowedException;
use blink\routing\exceptions\RouteNotFoundException;
use blink\core\HttpException;
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
    protected ?string $errorHandler;

    public function __construct(?string $errorHandler = null)
    {
        $this->errorHandler = $errorHandler;
    }

    public static function exceptionToArray($exception)
    {
        $array = [
            'name'    => get_class($exception),
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }

        $debug = config('app.debug');

        if ($debug) {
            $array['file']  = $exception->getFile();
            $array['line']  = $exception->getLine();
            $array['trace'] = explode("\n", $exception->getTraceAsString());
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = self::exceptionToArray($prev);
        }

        return $array;
    }

    public static function formatException(Throwable $e, Response $response)
    {
        if ($e instanceof HttpException) {
            $response->status($e->statusCode);
        } elseif ($e instanceof RouteNotFoundException) {
            $response->status(404);
        } elseif ($e instanceof MethodNotAllowedException) {
            $response->status(405);
        } else {
            $response->status(500);
        }

        $response->data = self::exceptionToArray($e);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $this->handleException($e);

            $resp = new Response();
            static::formatException($e, $resp);
            return $resp;
        }
    }

    protected function handleException(Throwable $e): void
    {
        if (! $this->errorHandler) {
            return;
        }

        /** @var ErrorHandler $handler */
        $handler = app()->get($this->errorHandler);
        $handler->handleException($e);
    }
}
