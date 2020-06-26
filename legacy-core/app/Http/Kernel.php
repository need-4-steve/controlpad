<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        'Fideloper\Proxy\TrustProxies',
        \App\Http\Middleware\Session::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // API middleware
        'api.log' => \App\Http\Middleware\Api\LogMiddleware::class,
        'api.cors' => \App\Http\Middleware\Api\CorsMiddleware::class,
        'api.auth' => \App\Http\Middleware\Api\AuthMiddleware::class,
        'api.roles' => \App\Http\Middleware\Api\RolesMiddleware::class,
        'api.org' => \App\Http\Middleware\Api\OrgMiddleware::class,
        'api.cpauth' => \App\Http\Middleware\Api\CPCommonAuth::class,
        // Other stuff
        'moat'         => \App\Http\Middleware\MoatMiddleware::class,
        'auth'         => \App\Http\Middleware\Authenticate::class,
        'auth.basic'   => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'     => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'          => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'        => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'     => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'isAdmin'      => \App\Http\Middleware\AdminMiddleware::class,
        'isSuperadmin' => \App\Http\Middleware\SuperadminMiddleware::class,
        'ios'          => \App\Http\Middleware\IosMiddleware::class,
        'store'        => \App\Http\Middleware\StoreMiddleware::class,
        'apiRole'      => \App\Http\Middleware\ApiRoleMiddleware::class,
        'jwt.auth'     => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
        'jwt.refresh'  => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        'cors'         => \App\Http\Middleware\Cors::class,
        'setting'      => \App\Http\Middleware\SettingsMiddleware::class,
        'userStatus'   => \App\Http\Middleware\UserStatusMiddleware::class
    ];
}
