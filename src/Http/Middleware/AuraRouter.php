<?php

namespace App\Http\Middleware;

use Aura\Router\Exception\ImmutableProperty;
use Aura\Router\Exception\RouteAlreadyExists;
use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class AuraRouter implements \Psr\Http\Server\MiddlewareInterface
{
    public function __construct(private readonly RouterContainer $router)
    {
    }

    /**
     * @throws RouteAlreadyExists
     * @throws ImmutableProperty
     */
    public function process(Request $request, Handler $handler): Response
    {
        $map = $this->router->getMap();

        // Protect the middleware from the included code
        (fn($map) => include getcwd() . '/config/routes.php')($map);

        $route = $this->router->getMatcher()->match($request);

        if ($route) {
            $request = $request->withAttribute('__handler', $route->handler);

            // Attach URL parameters to the request as attributes.
            foreach ($route->attributes as $key => $val) {
                $request = $request->withAttribute($key, $val);
            }
        }

        return $handler->handle($request);
    }
}
