<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LogPageChanges;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar middlewares globales aquí si es necesario
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
    
    $app->middleware([
        \App\Http\Middleware\LogUserActivity::class,
        'checkPermissions' => \App\Http\Middleware\CheckPermissions::class,
        App\Http\Middleware\LogPageChanges::class,

    ]);
    
   