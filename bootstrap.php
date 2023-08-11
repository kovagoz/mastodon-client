<?php

setlocale(LC_ALL, getenv('LOCALE') ?: 'en_US.UTF-8');

require __DIR__ . '/vendor/autoload.php';

return new \App\Container\Container();
