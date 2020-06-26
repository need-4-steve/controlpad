<?php

namespace App\Http\Middleware;

use App\Services\UserStatus\UserStatusService;
use Closure;

class UserStatusMiddleware
{
    public function __construct()
    {
        $this->userStatusService = new UserStatusService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $setting  Setting to check if allowed
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();
        if (!isset($user) && $permission == 'sell') {
            $user = session()->get('store_owner');
        }
        if ($this->userStatusService->checkPermission($user, $permission)) {
            return $next($request);
        }
        if ($permission === 'login') {
            auth()->logout();
            return redirect('/login');
        }
        if ($permission === 'buy' || $permission === 'sell' && auth()->check()) {
            return redirect('/dashboard');
        }
        if ($permission === 'sell' && isset($user)) {
            session()->forget('store_owner');
            return redirect($this->userStatusService->getSellRedirectUrl());
        }
        return $next($request);
    }
}
