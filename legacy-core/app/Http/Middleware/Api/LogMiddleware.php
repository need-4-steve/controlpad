<?php namespace App\Http\Middleware\Api;

class LogMiddleware
{
    public function handle($request, \Closure $next)
    {
        // TODO log incoming request
        $response = $next($request);
        // TODO log outgoing response
        return $response;
    }
}
