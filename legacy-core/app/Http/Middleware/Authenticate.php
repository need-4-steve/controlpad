<?php

namespace App\Http\Middleware;

use App\Services\Settings\SettingsService;
use App\Services\Authentication\JWTAuthService;
use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Authenticate
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
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!JWTAuthService::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            if ($request->is('api/*')) {
                return response('Unauthorized.', 401);
            }
            return redirect()->guest('login');
        }

        if ($request->user()->hasRole(['Admin', 'Superadmin'])) {
            return $next($request);
        }

        if (! request()->is('api/*') and ! request()->is('my-settings')) {
            $gracePeriod = $request->user()->lastSubscription->ends_at->addDays($this->settings->getGlobal('sub_grace_period', 'value'))->endOfDay()->toDateTimeString();
            if ($request->user()->hasRole(['Rep']) && empty($request->user()->lastSubscription) ||$gracePeriod < Carbon::now()->toDateTimeString()) {
                return redirect()->to('my-settings')->with('message_warning', 'To continue, you must have a current subscription.');
            }
            if ($request->user()->lastSubscription->ends_at === $request->user()->lastSubscription->created_at) {
                return redirect()->to('my-settings')->with('message_warning', 'To continue, you must have a current subscription.');
            }
        }

        return $next($request);
    }
}
