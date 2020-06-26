<?php

namespace App\Http\Middleware;

use Closure;

class CorporateMiddleware
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
        if (filter_var($request->input('corp'), FILTER_VALIDATE_BOOLEAN)) {
            $request['user_id'] = 1;
            $request['owner_id'] = 1;
        }
        return $next($request);
    }
}
