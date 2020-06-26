<?php

namespace App\Services\V0;

use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Services\Interfaces\V0\OrderServiceInterface;
use App\Services\V0\SubscriptionService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class OrderService implements OrderServiceInterface
{
    private $ordersUrl;
    private $headers;

    public function __construct(Request $request)
    {
        $this->ordersUrl = env('ORDERS_URL', 'https://orders.controlpadapi.com/api/v0');
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

    public function getCart($cartPid)
    {
        $ordersClient = new Client;
        try {
            $response = $ordersClient->get(
                $this->ordersUrl . '/carts/'.$cartPid.'?expands[]=lines',
                [
                    'headers' => $this->headers
                ]
            );
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                abort(400, 'Could not find a valid cart');
            }
            abort($e->getCode(), $e->getMessage());
        }
    }

    public function clearCart($cart)
    {
        $ordersClient = new Client;
        try {
            $response = $ordersClient->get(
                $this->ordersUrl . '/carts/'.$cart['pid'].'/empty',
                [
                    'headers' => $this->headers
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            app('log')->error('unable to clear cart', [
                'pid' => $cart['pid'],
                'message' => $e->getMessage(),
                'fingerprint' => 'unable to clear cart'
            ]);
        }
    }

    public function createCheckout($subscription, $buyer)
    {
        $autoship = (clone $subscription); // forces $autoship to be a different object
        SubscriptionService::calculateTotals($autoship);
        $autoship->billing_address = $buyer->billing_address;
        $autoship->shipping_address = $buyer->shipping_address;
        $autoship->shipping_is_billing = false;
        $ordersClient = new Client;
        try {
            $response = $ordersClient->post(
                $this->ordersUrl . '/checkouts/',
                [
                    'headers' => $this->headers,
                    'json' => $autoship
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            SubscriptionService::renewError($buyer, $subscription, $e);
        }
    }

    public function checkout($subscription, $buyer, $checkout)
    {
        $checkout->payment = (object) [
            'type' => 'card-token',
            'card_token' => $buyer->card->token,
            'amount' => (double) $checkout->total,
            'gateway_customer_id' => $buyer->card->gateway_customer_id
        ];
        $checkout->buyer = $buyer;
        $checkout->source = 'autoship';
        $ordersClient = new Client;
        try {
            $response = $ordersClient->post(
                $this->ordersUrl . '/checkouts/'.$checkout->pid.'/process',
                [
                    'headers' => $this->headers,
                    'json' => $checkout
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            SubscriptionService::renewError($buyer, $subscription, $e);
        }
    }
}
