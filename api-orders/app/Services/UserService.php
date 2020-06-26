<?php

namespace App\Services;

use CPCommon\Jwt\Jwt;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class UserService implements UserServiceInterface
{
    private $userApiUrl;

    public function __construct(Request $request)
    {
        $this->userApiUrl = env('USER_API_URL', 'https://users.controlpadapi.com/api/v0');
    }

    public function getBusinessAddressForUser($userPid)
    {
        return (object) app('db')->table('addresses as a')
            ->select('a.name', 'a.address_1 as line_1', 'a.address_2 as line_2', 'a.city', 'a.state', 'a.zip')
            ->join('users as u', function ($join) use ($userPid) {
                $join->on('u.id', '=', 'a.addressable_id')
                    ->where('a.addressable_type', '=', 'App\\Models\\User')
                    ->where('a.label', '=', 'Business')
                    ->where('u.pid', '=', $userPid);
            })
            ->first();
    }

    public function getUserById($id, $addresses = false)
    {
        $client = new Client;
        try {
            $response = $client->get(
                $this->userApiUrl . '/users/id/' . $id,
                [
                    'query' => [
                        'addresses' => $addresses,
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            $this->logException($re);
            abort(500);
        }
    }

    public function getUserbyPid($pid, $addresses = false)
    {
        $client = new Client;
        try {
            $response = $client->get(
                $this->userApiUrl . '/users/' . $pid,
                [
                    'query' => [
                        'addresses' => $addresses,
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            $this->logException($re);
            abort(500);
        }
    }

    public function findUserByEmail($email, $addresses = false)
    {
        $client = new Client;
        try {
            $response = $client->get(
                $this->userApiUrl . '/users/email/' . $email,
                [
                    'query' => [
                        'addresses' => $addresses,
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            if ($re->hasResponse() && $re->getResponse()->getStatusCode() === 404) {
                app('log')->debug('Check worked');
                // User not found
                return null;
            }
            $this->logException($re);
            abort(500);
        }
    }

    public function createCustomer($userId, $customer)
    {
        $client = new Client;
        try {
            $response = $client->post(
                $this->userApiUrl . '/customers',
                [
                    'json' => [
                        'user_id' => $userId,
                        'customer' => $customer
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            $this->logException($re);
            abort(500);
        }
    }

    public function attachCustomer($userId, $customerPid)
    {
        $client = new Client;
        try {
            $response = $client->post(
                $this->userApiUrl . '/customers/attach-by-pid',
                [
                    'json' => [
                        'user_id' => $userId,
                        'customer_pid' =>$customerPid
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            $this->logException($re);
            abort(500);
        }
    }

    private function logException($re)
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
