<?php

namespace App\Http\Middleware;

use Closure;
use CPCommon\Auth as CPAuth;

class Authenticate
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $authenticated = CPAuth::authenticate($request);
        if ($authenticated) {
            $request->user = $authenticated;
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
