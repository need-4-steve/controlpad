<?php

namespace App\Http\Middleware;

use Closure;

//use Illuminate\Http\Request;

class IosMiddleware
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
        if (!$request->_token and !$request->token) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
