<?php

namespace CPCommon;

use GuzzleHttp\Client;
use CPCommon\UserAuthorization;

class Auth
{
    public static function authenticate($request)
    {
        //MOAT AUTHENTICATION
        if ($request->headers->has('X-Cp-Secret')) {
            if (env('APP_SECRET', 'SECRET') != $request->header('X-Cp-Secret')) {
                app('log')->error($request->header('X-Cp-Request-Id') . ": Incorrect APP_SECRET");
                return null;
            }
            $orgId = $request->header('X-Cp-Org-Id');
            $sub = $request->header('X-Cp-Sub');
            $exp = $request->header('X-Cp-Exp');
            $role = $request->header('X-Cp-Role');
            $segmentId = $request->header('X-Cp-Segment_Id');
            $userPid = $request->header('X-Cp-User-Pid');
            if (empty($orgId) || empty($sub) || empty($exp) || empty($role) || empty($segmentId)) {
                app('log')->error($request->header('X-Cp-Request-Id') . ": " . print_r(['orgId' => $orgId, 'sub' => $sub, 'exp' => $exp, 'role' => $role, 'segmentId' => $segmentId], true));
                return null;
            }
            $token = [
                'sub' => $sub,
                'exp' => $exp,
                'role' => $role,
                'orgId' => $orgId,
                'userPid' => $userPid
            ];
            $authorized = Auth::createUserAuthorizationObject($token);
            $connectionInfo = [
                'read_host' => env('REGION', 'uw1') . "-db" . $segmentId . "00" . "01" . ".controlpad.com",
                'write_host' => env('REGION', 'uw1') . "-db" . $segmentId . "00" . "00" . ".controlpad.com",
                'db_name'=> $orgId . "_prod"
            ];
            Auth::switchDB($connectionInfo);
            return $authorized;
        }

        $authHeader = $request->header('authorization');
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
        } catch (\Predis\PredisException $ce) {
            $body = Auth::getAuthorizationFromAuthman($APIkey, $JWTtoken);
            $tenant = (empty($body) ? null : $body);
        }

        if (empty($tenant)) {
            return null;
        }
        if (isset($tenant['jwtToken'])) {
            $authorized = Auth::createUserAuthorizationObject($tenant['jwtToken']);
        } elseif (isset($tenant['org_id'])) {
            // Save org_id, api_key_id from api_key auth
            $authorized = new UserAuthorization(true,
                [
                    'sub' => 1,
                    'exp' => null,
                    'role' => 'Superadmin',
                    'userPid' => null,
                    'orgId' => $tenant['org_id']
                ]
            );
            $authorized->api_key_id = (isset($tenant['id']) ? $tenant['id'] : null);
        } else {
            // Old api key was cached and doesn't have org_id
            $authorized = Auth::createUserAuthorizationObject(null);
        }

        if ($authorized && $tenant) {
            Auth::switchDB($tenant);
        }
        return $authorized;
    }

    public static function authenticateGuest($domain = null)
    {
        try {
            $tenant = app('cache')->get('origin-'.$domain);
        } catch (\Predis\PredisException $ce) {
            // Ignore redis failure
            $tenant = null;
        }
        if (!$tenant) {
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
                $tenant = json_decode($response->getBody(), 1);
                try {
                    app('cache')->put('origin-'.$domain, $tenant, 30);
                } catch (\Predis\PredisException $ce) {
                    // Ignore redis connection failure
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        Auth::switchDB($tenant);
        return new UserAuthorization(false, [
            "sub" => null,
            "role" => 'Guest',
            "exp" => null,
            "orgId" => $tenant['org_id'],
            "userPid" => null
        ]);
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

    public static function switchDB($authDetails)
    {
        app('db')->purge(env('DB_CONNECTION', 'mysql'));
        config(['database.connections.'.env('DB_CONNECTION').'.read.host' => $authDetails['read_host']]);
        config(['database.connections.'.env('DB_CONNECTION').'.write.host' => $authDetails['write_host']]);
        config(['database.connections.'.env('DB_CONNECTION').'.database' => $authDetails['db_name']]);
        app('db')->connection(env('DB_CONNECTION', 'mysql'));
    }

    public static function createUserAuthorizationObject($JWTtoken = null)
    {
        if ($JWTtoken) {
            return new UserAuthorization(true, $JWTtoken);
        }
        return new UserAuthorization(true, [
            'sub' => 1,
            'exp' => null,
            'role' => 'Superadmin',
            'userPid' => null,
            'orgId' => null]);
    }
}
