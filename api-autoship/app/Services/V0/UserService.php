<?php

namespace App\Services\V0;

use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Services\Interfaces\V0\UserServiceInterface;
use App\Services\V0\SubscriptionService;
use DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class UserService implements UserServiceInterface
{
    private $ordersUrl;
    private $headers;

    public function __construct(Request $request)
    {
        $this->usersUrl = env('USERS_URL', 'https://users.controlpadapi.com/api/v0');
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        if ($request->hasHeader('X-Cp-Request-Id')) {
            $this->headers['X-Cp-Request-Id'] = $request->header('X-Cp-Request-Id');
        }
        if ($request->hasHeader('X-Cp-Secret')) {
            $this->headers['X-Cp-Secret'] = $request->header('X-Cp-Secret');
            $this->headers['X-Cp-Org-Id'] = $request->header('X-Cp-Org-Id');
            $this->headers['X-Cp-Sub'] = $request->header('X-Cp-Sub');
            $this->headers['X-Cp-Exp'] = $request->header('X-Cp-Exp');
            $this->headers['X-Cp-Role'] = $request->header('X-Cp-Role');
            $this->headers['X-Cp-Segment_Id'] = $request->header('X-Cp-Segment_Id');
            $this->headers['X-Cp-User-Pid'] = $request->header('X-Cp-User-Pid');
        } elseif ($request->hasHeader('Authorization')) {
            $this->headers['Authorization'] = $request->header('Authorization');
        } elseif ($request->hasHeader('APIKey')) {
            $this->headers['APIKey'] = $request->header('APIKey');
        }
    }

    public function getBuyer($buyerPid, $subscription)
    {
        $usersClient = new Client;
        try {
            $response = $usersClient->get(
                $this->usersUrl . '/users/'.$buyerPid.'?addresses=1',
                [
                    'headers' => $this->headers
                ]
            );
            $buyer = json_decode($response->getBody());
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                SubscriptionService::createAttempt($subscription, 'user not found', 'failure');
                abort(404, 'user not found');
            }
            SubscriptionService::renewError($buyer, $subscription, $e);
        }
        if (empty($buyer->shipping_address->line_1) ||
            empty($buyer->shipping_address->city)   ||
            empty($buyer->shipping_address->state)  ||
            empty($buyer->shipping_address->zip)
            ) {
                SubscriptionService::renewError($buyer, $subscription, null, 'shipping address missing or invalid', 422);
        }
        if (empty($buyer->billing_address->zip)) {
            SubscriptionService::renewError($buyer, $subscription, null, 'billing address missing or invalid', 422);
        }
        try {
            $response = $usersClient->get(
                $this->usersUrl . '/users/'.$buyerPid.'/card-token',
                [
                    'headers' => $this->headers
                ]
            );
            $card = json_decode($response->getBody());
            $buyer->card = $card;
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                SubscriptionService::renewError($buyer, $subscription, $e, 'card not on file', 422);
            }
            SubscriptionService::renewError($buyer, $subscription, $e);
        }
        return $buyer;
    }

    public function getUser($buyerPid, $subscription)
    {
        $usersClient = new Client;
        try {
            $response = $usersClient->get(
                $this->usersUrl . '/users/'.$buyerPid.'?addresses=1',
                [
                    'headers' => $this->headers
                ]
            );
            $buyer = json_decode($response->getBody());
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                abort(404, 'user not found');
            }
            $message = (string) $e->getResponse()->getBody();
            abort(500, $message);
        }
        try {
            $response = $usersClient->get(
                $this->usersUrl . '/users/'.$buyerPid.'/card-token',
                [
                    'headers' => $this->headers
                ]
            );
            $card = json_decode($response->getBody());
            $buyer->card = $card;
        } catch (RequestException $e) {

        }
        return $buyer;
    }
}
