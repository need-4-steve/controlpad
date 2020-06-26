<?php

namespace App\Services\Shippo\Classes;

use GuzzleHttp;

class Shippo_Order
{
    public static function all($params = null)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->get('https://api.goshippo.com/orders/', [
            'headers' => [
                'Authorization' => 'ShippoToken '.env('SHIPPO_API_KEY')
            ]
        ]);
        return json_decode($response->getBody(), 1);
    }

    public static function create($params = null)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->post('https://api.goshippo.com/orders/', [
            'headers' => [
                'Authorization' => 'ShippoToken '.env('SHIPPO_API_KEY'),
            ],
            'form_params' => $params
        ]);
        return json_decode($response->getBody(), 1);
    }

    public static function retrieve($id)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->get('https://api.goshippo.com/orders/'.$id, [
            'headers' => [
                'Authorization' => 'ShippoToken '.env('SHIPPO_API_KEY')
            ]
        ]);
        return json_decode($response->getBody(), 1);
    }
}
