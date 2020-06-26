<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class StoreMiddleware
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
        // find the subdomain
        $pieces = explode('.', request()->getHost());
        $subdomain = $pieces[0];
        if ($subdomain === strstr(env('APP_URL'), '.', true) || $subdomain === strstr(env('STORE_DOMAIN'), '.', true)) {
            session()->forget('store_owner');
            return $next($request);
        }
        if (session()->get('store_owner.public_id') === $subdomain) {
            return $next($request);
        }
        $store_owner = User::where('public_id', $subdomain)->first();
        session()->put('store_owner', $store_owner);
        return $next($request);
    }
}
