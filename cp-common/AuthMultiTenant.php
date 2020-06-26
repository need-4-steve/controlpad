<?php

namespace CPCommon;

use GuzzleHttp\Client;
use CPCommon\UserAuthorization;

class AuthMultiTenant
{
    public static function authenticate($request)
    {
        $authHeader = $request->headers->get('authorization');
        $JWTtoken = null;
        if ($authHeader && preg_match('/bearer\s*(\S+)\b/i', $authHeader, $matches)) {
            $JWTtoken = $matches[1];
        }
        $APIkey = $request->header('APIKey');
        $authorized = null;
        $tenant = null;
        // make sure there is a token or key
        if ($JWTtoken) {
            $cacheKey = $JWTtoken;
        } elseif ($APIkey) {
            $cacheKey = $APIkey;
        } else {
            return null;
        }
        // check cache or make a new call
        try {
            $tenant = app('cache')->get($cacheKey, function () use ($APIkey, $JWTtoken, $cacheKey) {
                $body = Auth::getAuthorizationFromAuthman($APIkey, $JWTtoken);
                if (empty($body)) {
                    return null;
                }
                app('cache')->put($cacheKey, $body, 10);
                return $body;
            });
        } catch(\Predis\PredisException $ce) {
            $body = Auth::getAuthorizationFromAuthman($APIkey, $JWTtoken);
            $tenant = (empty($body) ? null : $body);
        }

        if (empty($tenant)) {
            return null;
        }
        $authorized = Auth::createUserAuthorizationObject(isset($tenant['jwtToken']) ? $tenant['jwtToken'] : null);
        return $authorized;
    }

    public static function authenticateGuest($domain = null)
    {
        try {
            $tenant = app('cache')->get('origin-'.$domain);
        } catch(\Predis\PredisException $ce) {
            // Ignore redis failure
            $tenant = null;
        }
        if ($tenant) {
            return new UserAuthorization(false);
        }
        $client = new Client;
        try {
            $response = $client->request(
                'POST',
                env('AUTHMAN_URL') . '/api/v0/find-tenant-by-domain',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'APIKey' => env('AUTHMAN_API_KEY'),
                        'Cache-Control' => 'no-cache',
                    ],
                    'body' => json_encode([
                        'domain' => $domain
                    ])
                ]
            );
            $body = json_decode($response->getBody(), 1);
            try {
                app('cache')->put('origin-'.$domain, $body, 30);
            } catch(\Predis\PredisException $ce) {
                // Ignore redis failure
            }
            return new UserAuthorization(false);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getAuthorizationFromAuthman($APIkey, $JWTtoken)
    {
        $client = new Client;
        try {
            $response = $client->request(
                'POST',
                env('AUTHMAN_URL') . '/api/v0/apikeys/auth',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'APIKey' => env('AUTHMAN_API_KEY'),
                        'Cache-Control' => 'no-cache',
                    ],
                    'body' => json_encode([
                        'key' => $APIkey,
                        'token' => $JWTtoken,
                        'service' => env('AUTHMAN_SERVICE_ID')
                    ])
                ]
            );
            return json_decode($response->getBody(), 1);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function createUserAuthorizationObject($JWTtoken = null)
    {
        if ($JWTtoken) {
            return new UserAuthorization(true, $JWTtoken);
        }
        return new UserAuthorization(true, [
            'sub' => 1,
            'exp' => null,
            'userPid' => null,
            'role' => 'Superadmin']);
    }
}
