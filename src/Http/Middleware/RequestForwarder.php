<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class RequestForwarder implements MiddlewareInterface
{
    public const RESPONSE_HEADER = 'X-Internal-Redirect';

    public function process(Request $request, Handler $handler): Response
    {
        do {
            // Response already exists, it means that forward happened before
            if (isset($response)) {
                $request = $request->withAttribute(
                    '__handler',
                    $response->getHeaderLine(self::RESPONSE_HEADER)
                );
            }

            $response = $handler->handle($request);
        } while ($response->hasHeader(self::RESPONSE_HEADER));

        return $response;
    }
}
