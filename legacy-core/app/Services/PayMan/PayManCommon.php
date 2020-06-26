<?php namespace App\Services\PayMan;

use Log;
use GuzzleHttp;
use Request;
use App\Repositories\Eloquent\PromotionRepository;
use Guzzle\Http\Exception\BadResponseException as GuzzleBadResponseException;

class PayManCommon
{
    public function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'APIKey ' . env('PAYMAN_KEY'),
            'Cache-Control' => 'no-cache'
        ];
    }

    public function get($path)
    {
        try {
            $url = env('PAYMAN_URL') . '/' . $path;
            $client = new GuzzleHttp\Client();
            $res = $client->get($url, [
                'headers' => $this->getHeaders()
            ]);
            return json_decode($res->getBody(), 1);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred: ' . $path);
            return $e->getMessage();
        }
    }

    public function getWithQuery($path, $query)
    {
        $query['headers'] = $this->getHeaders();
        try {
            $url = env('PAYMAN_URL') . '/' . $path;
            $client = new GuzzleHttp\Client();
            $res = $client->get($url, $query);
            return json_decode($res->getBody(), 1);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred: ' . $path);
            return response()->json($e->getMessage(), 500)->send();
        }
    }

    public function getWithQueryNoJson($path, $query)
    {
        $query['headers'] = $this->getHeaders();
        try {
            $url = env('PAYMAN_URL') . '/' . $path;
            $client = new GuzzleHttp\Client();
            $res = $client->get($url, $query);
            return $res->getBody();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred: ' . $path);
            return $e->getMessage();
        }
    }

    public function post($path, $data)
    {
        try {
            $url = env('PAYMAN_URL') . '/' . $path;
            $client = new GuzzleHttp\Client();
            $res = $client->post($url, [
                'headers' => $this->getHeaders(),
                'body' => json_encode($data)
            ]);
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $explodedResponse = explode('response:', $message);
            return ['error_code' => $code, 'body' => trim(preg_replace('/[^A-Za-z0-9]/', ' ', end($explodedResponse)))];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Total up transaction amounts
    |--------------------------------------------------------------------------
    |
    | This can be used to calculate totals from that aren't provided by
    | default in the PayMan reports.
    |
    */
    public function totalTransactionAmounts($data)
    {
        if (isset($data['payouts'])) {
            $data['fees'] = 0;
            $data['cashSale'] = 0;
            $data['netAmount'] = 0;
            foreach ($data['payouts'] as $payout) {
                if ($payout['type'] == 'fee') {
                    $data['fees'] += $payout['amount'];
                } elseif ($payout['type'] == 'cash-tax' || $payout['type'] == 'cash-fee') {
                    $data['cashSale'] += $payout['amount'];
                } elseif ($payout['type'] == 'e-wallet' || $payout['type'] == 'merchant') {
                    $data['netAmount'] += $payout['amount'];
                }
            }
        }
        return $data;
    }

    public function convertPagination($response)
    {
        return [
            'current_page' => $response['currentPage'],
            'per_page' => $response['perPage'],
            'last_page' => $response['totalPage'],
            'total' => $response['total'],
            'data' => $response['data']
        ];
    }
}
