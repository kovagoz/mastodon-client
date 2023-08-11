<?php

namespace App\Container;

use App\Http\HttpServiceProvider;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private \Illuminate\Container\Container $container;
    private array $providers = [
        HttpServiceProvider::class,
    ];

    public function __construct()
    {
        $this->container = new \Illuminate\Container\Container();
        $this->container->instance(ContainerInterface::class, $this);

        // Handle the Singleton annotation on classes
        $this->container->beforeResolving(function ($abstract) {
            if ($this->isNotRegisteredButExists($abstract)) {
                $attr = (new \ReflectionClass($abstract))->getAttributes(Singleton::class);

                if (count($attr) > 0) {
                    $this->container->singleton($abstract);
                }
            }
        });

        $this->registerServiceProviders();
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    private function registerServiceProviders(): void
    {
        /** @var string $providerClass */
        foreach ($this->providers as $providerClass) {
            $provider = $this->container->get($providerClass);
            /** @var ServiceProvider $provider */
            $provider->register($this->container);
        }
    }

    private function isNotRegisteredButExists(string $class): bool
    {
        return class_exists($class) && !$this->container->has($class);
    }
}
