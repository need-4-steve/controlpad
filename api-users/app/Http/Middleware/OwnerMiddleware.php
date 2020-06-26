<?php

namespace App\Http\Middleware;

use Closure;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user->hasRole(['Admin', 'Superadmin'])) {
            return $next($request);
        }
        // Locks down users only changing resources that they own.
        // the route parameters are last in the array for $request->route()
        $routeParameters = array_slice($request->route(), -1)[0];
        if (array_key_exists('pid', $routeParameters) && $request->user->pid === $routeParameters['pid']) {
            return $next($request);
        }
        if (array_key_exists('id', $routeParameters) && $request->user->id == $routeParameters['id']) {
            return $next($request);
        }
        abort(403, 'Admin or owner only');
    }
}
