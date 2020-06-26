<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JsonMiddleware
{
    /**
     * Verify valid json body only
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isJson()) {
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new HttpException(400, 'Malformed request body');
            }
        } else {
            throw new HttpException(415, 'Content-Type application/json only');
        }
        return $next($request);
    }
}
