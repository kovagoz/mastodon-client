<?php

return [
    // From the outer to the inner one
    \App\Http\Middleware\ErrorHandler::class,
    \App\Http\Middleware\AuraRouter::class,
    \App\Http\Middleware\RequestForwarder::class,
    \App\Http\Middleware\ClosureHandler::class,
    \App\Http\Middleware\RequestDispatcher::class,
];
