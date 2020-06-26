<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use App\Repositories\Eloquent\V0\ApiKeyRepository;

class AuthenticateAPIKey
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
        // Get API Key from header
        $token = $request->header('APIKey');
        if (!isset($token) || $token == '') {
            return response('Unauthorized.', 401);
        }
        // Authenticate API Key
        $ApiKeyRepo = new ApiKeyRepository;
        $key = $ApiKeyRepo->authenticate($token, 1);
        if (is_null($key)) {
            return response('Unauthorized.', 401);
        }
        // TODO: log reporting information
        return $next($request);
    }
}
