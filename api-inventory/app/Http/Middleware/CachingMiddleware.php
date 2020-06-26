<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Carbon\Carbon;

class CachingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $seconds = null)
    {
        $seconds = is_null($seconds) ? env('ROUTE_CACHING_TIME', 60) : $seconds;
        // Don't cache if the ROUTE_CACHING is false or $seconds is 0
        if (!env('ROUTE_CACHING', false) || $seconds === 0) {
            return $next($request);
        }
        $time = Carbon::now()->addSeconds($seconds);
        // Don't cache if the user is admin
        if ($request->user->role === 'Admin' || $request->user->role === 'Superadmin') {
            return $next($request);
        }
        // Don't cache if the resource is owned by the user
        if ($request->input('user_id') == $request->user->id || $request->input('user_pid') === $request->user->pid) {
            return $next($request);
        }
        $path = $request->getPathInfo();
        $queryParams = $request->query();

        // Sort and rebuild the query parameters so that no matter what order the parameters are, it will be from the same cache
        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        $fullPath = "{$path}?{$queryString}";

        // cache it by tenant and hash the full path to have a shortened key
        $key = 'tenant:'.$request->user->orgId.':route_cache:'.sha1($fullPath);
        try {
            return Cache::remember($key, $time, function () use ($request, $next) {
                return $next($request);
            });
        } catch (\Predis\PredisException $ce) {
            // If redis fails we still want to get something, it will be slow
            return $next($request);
        }
    }
}
