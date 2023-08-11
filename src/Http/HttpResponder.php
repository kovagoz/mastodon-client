<?php

namespace App\Http;

use App\Container\Singleton;
use App\Http\Middleware\RequestForwarder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

#[Singleton]
class HttpResponder
{
    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface   $streamFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    /**
     * Create PSR response from various type of messages
     *
     * @param mixed $message
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function reply(mixed $message = null): ResponseInterface
    {
        if ($message instanceof ResponseInterface) {
            return $message;
        }

        if ($message === null) {
            return $this->replyNoContent();
        }

        if (is_array($message)) {
            return $this->replyJson($message);
        }

        if (is_string($message)) {
            return $this->replyHtml($message);
        }

        throw new \InvalidArgumentException();
    }

    public function replyHtml(string $html): ResponseInterface
    {
        $stream = $this->streamFactory->createStream($html);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'text/html')
            ->withBody($stream);
    }

    public function replyText(string $text): ResponseInterface
    {
        $stream = $this->streamFactory->createStream($text);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'plain/text')
            ->withBody($stream);
    }

    /**
     * @throws \JsonException
     */
    public function replyJson(array $data): ResponseInterface
    {
        $message = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $stream  = $this->streamFactory->createStream($message);

        return $this->responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }

    public function replyNoContent(): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse()
            ->withStatus(204)
            ->withHeader('Content-Type', 'text/html');
    }

    /**
     * Redirect client to another location
     */
    public function redirect(string|UriInterface $url): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', (string) $url);
    }

    /**
     * Pass the request to another handler (internal redirect).
     */
    public function forward(string $handler): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse()
            ->withHeader(RequestForwarder::RESPONSE_HEADER, $handler);
    }
}
