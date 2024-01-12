<?php

declare(strict_types=1);

namespace blink\routing\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use blink\http\Response;

/**
 * Class CallbackHandler
 *
 * @package blink\routing
 */
class CallbackHandler implements RequestHandlerInterface
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $resp = ($this->callback)();

        if (! $resp instanceof ResponseInterface) {
            $resp = (new Response())->with($resp);
        }

        return $resp;
    }
}
