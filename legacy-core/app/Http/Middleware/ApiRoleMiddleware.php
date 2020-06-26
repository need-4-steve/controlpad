<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Authentication\JWTAuthService;

class ApiRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  Role name to check for
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!JWTAuthService::check()) {
            return response()->json(['You need to sign in to complete this request'], HTTP_UNAUTHORIZED);
        }

        if (ucfirst($role) === 'Admin') {
            $role = ['Superadmin', 'Admin'];
        }

        if (! auth()->user()->hasRole($role)) {
            return response()->json(['You do not have permission to complete this request'], HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
