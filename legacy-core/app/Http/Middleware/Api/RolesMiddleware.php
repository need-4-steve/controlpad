<?php namespace App\Http\Middleware\Api;

use App\Services\Authentication\JWTAuthService;

class RolesMiddleware
{
    public function handle($request, \Closure $next, ...$roles)
    {
        $claims = JWTAuthService::verify();
        $role = $claims['role'];

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array(strtolower($role), $roles)) {
            return response()->json('Forbidden', 403);
            // abort(403);
        }

        return $next($request);
    }
}
