<?php namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class PaymanService implements PaymanServiceInterface
{
    private $paymanUrl;
    private $client;

    public function __construct(Request $request)
    {
        $this->paymanUrl = env('PAYMAN_URL', 'https://paymanlb.controlpad.com');
        $this->client = new Client;
    }

    public function authorizePayment($teamId, $payee, $payer, $payment, $tax, $shipping, $discount, $orderPid, $description, $affiliatePayouts = null)
    {
        $requestBody = [
            'teamId' => $teamId,
            'payeeUserId' => $payee->id,
            'payerUserId' => $payer->id,
            'buyerName' => $payer->first_name.' '.$payer->last_name,
            'amount' => $payment['amount'],
            'salesTax' => $tax,
            'orderId' => $orderPid,
            'shipping' => $shipping,
            'discount' => $discount,
            'description' => $description,
            'statusCode' => 'A', // Requests auth only
        ];
        switch ($payment['type']) {
            case 'cash':
                $requestBody['transactionType'] = 'cash-sale';
                break;
            case 'card':
                $requestBody['transactionType'] = 'credit-card-sale';
                $requestBody['card'] = $payment['card'];
                $requestBody['accountHolder'] = $payment['card']['name'];
                break;
            case 'card-token':
                $requestBody['transactionType'] = 'credit-card-sale';
                $requestBody['card']['token'] = $payment['card_token'];
                $requestBody['card']['gatewayCustomerId'] = (isset($payment['gateway_customer_id']) ? $payment['gateway_customer_id'] : null);
                break;
            case 'import':
                $requestBody['gatewayReferenceId'] = $payment['import_id'];
                break;
            case 'e-wallet':
                $requestBody['teamId'] = 'rep';
                $requestBody['transactionType'] = 'e-wallet-sale';
                break;
            default:
                app('log')->error('Unexpected payment type: ' . $payment['type']);
                return ['result_code' => 98, 'result' => 'Payment type invalid'];
                break;
        }
        if (!empty($affiliatePayouts)) {
            $requestBody['affiliatePayouts'] = $affiliatePayouts;
        }
        try {
            $response = $this->client->post(
                $this->paymanUrl . '/transactions',
                [
                    'json' => $requestBody,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            app('log')->error($e);
            return (object)['resultCode' => 99, 'result' => 'Unexpected payment error.'];
        }
    }

    public function captureTransaction($transactionId, $receiptId)
    {
        try {
            $response = $this->client->post(
                $this->paymanUrl . '/transactions/capture',
                [
                    'json' => [
                        'id' => $transactionId,
                        'orderId' => $receiptId
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            app('log')->error($e);
            return (object)['resultCode' => 99, 'result' => 'Unexpected payment error.'];
        }
    }

    public function cancelTransaction($transactionId, $amount)
    {
        $requestBody = [
            'forTxnId' => $transactionId,
            'amount' => $amount,
            'transactionType' => 'refund'
        ];
        try {
            $response = $this->client->post(
                $this->paymanUrl . '/transactions',
                [
                    'json' => $requestBody,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            app('log')->error($e);
            return null;
        }
    }
}
