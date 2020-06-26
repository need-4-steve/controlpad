<?php namespace App\Http\Middleware\Api;

use App\Services\Authentication\JWTAuthService;

class AuthMiddleware
{
    public function handle($request, \Closure $next)
    {
        try {
            JWTAuthService::verify();
            JWTAuthService::tokenCheck();
            if (auth()->user() === null) {
                abort(401);
            }
        } catch (\Exception $e) {
            auth()->logout();
            abort(401);
        }
        return $next($request);
    }
}
