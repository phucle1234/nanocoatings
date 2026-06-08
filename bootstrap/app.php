<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return route('home');
            return $user->role === 'dealer'
                ? route('dealer.dashboard')
                : route('customer.dashboard');
        });

        // Đăng ký middleware cho Backpack
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckIfAdmin::class,
            'setlocale' => \App\Http\Middleware\SetLocale::class,
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'dealer'   => \App\Http\Middleware\EnsureDealer::class,
            'api.request.log' => 'App\\Http\\Middleware\\LogIncomingApiRequest'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
