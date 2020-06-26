<?php

namespace App\Http\Middleware;

use Closure;

class SettingsMiddleware
{
    public function __construct()
    {
        $this->settings = app('globalSettings');
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $setting  Setting to check if allowed
     * @return mixed
     */
    public function handle($request, Closure $next, $setting)
    {
        $setting = $this->settings->getGlobal($setting, 'show');
        if ($setting == true || $setting == 'true' || auth()->check() && auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            return $next($request);
        }

        return response()->json(['This feature has been disabled by the administrator.'], HTTP_FORBIDDEN);
    }
}
