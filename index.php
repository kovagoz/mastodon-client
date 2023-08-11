<?php

use App\Http\Middleware\MiddlewareStack;
use Psr\Http\Message\ResponseInterface;

$container = require __DIR__ . '/bootstrap.php';

$request = $container->get(\Psr\Http\Message\ServerRequestInterface::class);
$request = $request->withAttribute('__handler', \App\Http\Controller\NotFoundController::class);

/** @var ResponseInterface $response */
$response = $container->get(MiddlewareStack::class)->handle($request);

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
