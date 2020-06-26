<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use GuzzleHttp\Client;
use CPCommon\Auth as CPAuth;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class CPCommonAuth
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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
        $authenticated = CPAuth::authenticate($request);
        if ($authenticated) {
            $request->user = $authenticated;
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
