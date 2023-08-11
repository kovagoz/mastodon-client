<?php

namespace App\Http\Controller;

use App\Http\HttpResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class IndexController implements RequestHandlerInterface
{
    public function __construct(private readonly HttpResponder $responder)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder->replyHtml('<h1>Hi there!</h1>');
    }
}
