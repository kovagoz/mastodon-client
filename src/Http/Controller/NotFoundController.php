<?php

namespace App\Http\Controller;

use App\Http\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundController implements RequestHandlerInterface
{
    public function __construct(
        private readonly HttpResponder $responder,
    ){
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder
            ->replyHtml('<h1>Page not found</h1>')
            ->withStatus(404);
    }
}
