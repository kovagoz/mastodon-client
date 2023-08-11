<?php

namespace App\Http;

use App\Container\ServiceProvider;
use App\Http\Middleware\MiddlewareStack;
use Aura\Router\Generator;
use Aura\Router\RouterContainer;
use Illuminate\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(HttpResponder::class);

        // PSR-7 implementation
        $container->singleton(ResponseFactoryInterface::class, Psr17Factory::class);
        $container->singleton(StreamFactoryInterface::class, Psr17Factory::class);
        $container->singleton(
            ServerRequestInterface::class,
            function (ContainerInterface $container) {
                $psr17Factory = $container->get(Psr17Factory::class);

                $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
                    $psr17Factory, // ServerRequestFactory
                    $psr17Factory, // UriFactory
                    $psr17Factory, // UploadedFileFactory
                    $psr17Factory  // StreamFactory
                );

                return $creator->fromGlobals();
            }
        );

        // Aura router
        $container->singleton(RouterContainer::class);
        $container->singleton(Generator::class, function (ContainerInterface $container) {
            return $container->get(RouterContainer::class)->getGenerator();
        });

        // Middleware stack
        $container->singleton(
            MiddlewareStack::class,
            function (ContainerInterface $container) {
                $defaultResponse = $container->get(HttpResponder::class)
                    ->reply('No applicable handler found')
                    ->withStatus(500);

                $stack = new MiddlewareStack($defaultResponse);

                $middlewares = include sprintf('%s/config/middlewares.php', getcwd());

                foreach (array_reverse($middlewares) as $class) {
                    $stack->push($container->get($class));
                }

                return $stack;
            }
        );
    }
}
