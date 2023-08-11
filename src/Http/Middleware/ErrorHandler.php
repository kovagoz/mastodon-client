<?php

namespace App\Http\Middleware;

use App\Http\HttpResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ErrorHandler implements MiddlewareInterface
{
    public function __construct(private readonly HttpResponder $responder)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $throwable) {
            // Pretty backtrace is available only in dev env
            if ($this->isDevelopmentEnvironment()) {
                $whoops = new \Whoops\Run;
                $whoops->allowQuit(false);
                $whoops->writeToOutput(false);
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());

                $response = $this->responder->reply(
                    $whoops->handleException($throwable)
                );
            } else {
                // TODO: log the error
                $response = $this->responder->reply('An error occurred');
            }

            return $response->withStatus(500);
        }
    }

    private function isDevelopmentEnvironment(): bool
    {
        return class_exists(\Whoops\Run::class);
    }
}
