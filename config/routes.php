<?php

/** @var \Aura\Router\Map $map */

$map->get('frontpage', '/', \App\Http\Controller\IndexController::class);
$map->get('health', '/healthz', fn() => 'ok');
