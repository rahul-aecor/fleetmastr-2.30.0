<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Barryvdh\Cors\HandleCors::class,
        \App\Http\Middleware\XssSanitizer::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'XssSanitizer' => \App\Http\Middleware\XssSanitizer::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        // 'can' => \Spatie\Authorize\Middleware\Authorize::class,
        'can' => \App\Http\Middleware\Authorize::class,
        'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
        'incident.report' => \App\Http\Middleware\IncidentReports::class,
        'telematics' => \App\Http\Middleware\Telematics::class,
        'frameGuard' => \App\Http\Middleware\FrameGuard::class,
        'cors' => App\Http\Middleware\Cors::class,

    ];
}
