<?php

namespace App\Container;

interface ServiceProvider
{
    public function register(\Illuminate\Container\Container $container): void;
}
