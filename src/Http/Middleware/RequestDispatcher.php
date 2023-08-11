<?php

namespace App\Http\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * @see \Test\RequestDispatcherTest
 */
class RequestDispatcher implements MiddlewareInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(Request $request, Handler $handler): Response
    {
        $requestHandler = $request->getAttribute('__handler');

        // No handler defined, skip this middleware
        if ($requestHandler === null) {
            return $handler->handle($request);
        }

        $handler = $this->container->get($requestHandler);

        return $handler->handle($request);
    }
}
