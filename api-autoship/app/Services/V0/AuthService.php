<?php

namespace App\Services\V0;

use App\Jwt;
use App\Services\Interfaces\V0\AuthServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthService implements AuthServiceInterface
{
    public function getTenants()
    {
        $client = new Client();
        try {
            $response = $client->request('GET', env('AUTHMAN_URL').'/api/v0/tenants', [
                'headers' => [
                    'APIKey' => env('AUTHMAN_API_KEY')
                ],
            ]);
            return json_decode($response->getBody())->data;
        } catch (RequestException $e) {
            Abort(500, $e->getMessage());
        }
    }
}
