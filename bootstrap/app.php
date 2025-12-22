<?php

use App\Http\Middleware\PublicApiTokenMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Sanctum;

// ✅ ADD THIS - Force Sanctum to use custom model early
Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);
//ItemMasterModel::useItemMasterModel(\App\Models\ItemMasterModel::class);


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       $middleware->alias([
         'api.token' => PublicApiTokenMiddleware::class,
         ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
