<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use CPCommon\Auth as CPAuth;

class PublicMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    protected $jwt;

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
        // determine if attempting authentication
        $jwt = $request->headers->get('authorization');
        $key = $request->header('APIKey');

        // create a guest user by origin domain
        if (!$key && !$jwt) {
            $origin = $request->headers->get('Origin');
            $guest = CPAuth::authenticateGuest($origin);
            if ($guest) {
                $request->user = $guest;
                return $next($request);
            }
        }

        // attempt to authenticate a user
        $authenticated = CPAuth::authenticate($request);
        if ($authenticated) {
            $request->user = $authenticated;
            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
