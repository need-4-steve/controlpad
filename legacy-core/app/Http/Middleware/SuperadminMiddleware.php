<?php

namespace App\Http\Middleware;

use Closure;

class SuperadminMiddleware
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
        if (! $request->user() or ! $request->user()->hasRole(['Superadmin'])) {
            return redirect('//' . config('app.url'))->with('message', 'You do not have the authority to do that.');
        }
        return $next($request);
    }
}
