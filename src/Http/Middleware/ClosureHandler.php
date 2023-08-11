<?php

namespace App\Http\Middleware;

use App\Http\HttpResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ClosureHandler implements MiddlewareInterface
{
    public function __construct(private readonly HttpResponder $responder)
    {
    }

    /**
     * @throws \JsonException
     */
    public function process(Request $request, Handler $handler): Response
    {
        $requestHandler = $request->getAttribute('__handler');

        if ($requestHandler instanceof \Closure) {
            return $this->responder->reply($requestHandler($request));
        }

        return $handler->handle($request);
    }
}
