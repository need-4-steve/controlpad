<?php

namespace App;

use CPCommon\Jwt\Jwt;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class Utils
{
    private $clientMap = [];
    private $clientJwtMap = [];

    public function getJWTAuthHeader($orgId)
    {
        if (!array_key_exists($orgId, $this->clientJwtMap) ||
            $this->clientJwtMap[$orgId]['claims']['exp'] > (time() - 20)
        ) {
            // Make a new jwt if missing or about to expire
                $this->createJwtInfo($orgId);
        }
        return 'Bearer ' . $this->clientJwtMap[$orgId]['jwt'];
    }

    private function createJwtInfo($orgId)
    {
        $claims = [
            'exp' => time() + 300,
            'iat' => time(),
            'iss' => 'api.controlpad.com',
            'aud' => 'api.controlpad.com',
            'sub' => 1,
            'role' => 'Admin',
            'orgId' => $orgId,
            'userPid' => null
        ];
        $jwt = Jwt::sign($claims, env('JWT_SECRET'));
        $this->clientJwtMap[$orgId] = ['claims' => $claims, 'jwt' => $jwt];
    }

    public function getClientInfo($orgId)
    {
        if (!array_key_exists($orgId, $this->clientMap) ||
            $this->clientMap[$orgId]->exp > (time() - 20)
        ) {
            $this->clientMap[$orgId] = $this->findClient($orgId);
            $this->clientMap[$orgId]->exp = time() + 300; // Cache the client for 5 minutes to reduce calls but allow changes to take effect
        }
        return $this->clientMap[$orgId];
    }

    private function findClient($orgId)
    {
        try {
            $client = new Client;
            $headers = [
                'Content-Type' => 'application/json',
                'APIKey' => env('AUTHMAN_API_KEY')
            ];

            $response = $client->get(
                env('AUTHMAN_URL') . '/api/v0/tenants/' . $orgId,
                [
                    'headers' => $headers
                ]
            );
            $body = json_decode($response->getBody());
            return $body;
        } catch (RequestException $re) {
            $this->logGuzzleException($re);
            return null;
            // TODO how should we handle failures?
            // For now we are just dropping them
        }
    }

    public function logGuzzleException($re)
    {
        if ($re->hasResponse()) {
            $responseBody = Psr7\str($re->getResponse());
        } else {
            $responseBody = null;
        }
        app('log')->error(
            $re,
            [
                'request' => Psr7\str($re->getRequest()),
                'response' => $responseBody
            ]
        );
    }
}
