<?php
namespace App\Services\PayMan;

use Auth;
use Config;
use GuzzleHttp;
use Input;
use Log;
use Request;
use Validator;
use View;
use App\Models\Banking;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Settings\SettingsService;
use App\Exceptions\APIResponseException;
use Carbon\Carbon;

class PayManService extends PayManCommon
{
    /***********************************
    * get banking information for user
    ************************************/
    public function getBanking($user_id)
    {
        $client = new GuzzleHttp\Client();
        try {
            $url = env('PAYMAN_URL')
                . '/user-accounts/'
                . $user_id;

            $res = $client->get(
                $url,
                [
                    'headers' => $this->getHeaders()
                ]
            );
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            logger()->error([
                'class PaymanService function getBanking',
                $e->getMessage(),
                'user_id' => $user_id
            ]);
            return response()->json($e->getMessage(), HTTP_BAD_REQUEST)->send();
        }
    }

    /***********************************
    * get banking information for all users (superadmin)
    ************************************/
    public function getBankingAllUsers($page = 1, $count = 100)
    {
        $client = new GuzzleHttp\Client();
        try {
            $url = env('PAYMAN_URL')
                . '/user-accounts?page=' . $page . '&count=' . $count;

            $res = $client->get(
                $url,
                [
                    'headers' => $this->getHeaders()
                ]
            );
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            logger()->error([
                'class PaymanService function getBankingAllUsers',
                $e->getMessage()
            ]);
            return response()->json($e->getMessage(), HTTP_BAD_REQUEST)->send();
        }
    }


    /************************************
    * update user's banking information
    *************************************/
    public function updateOrCreateBanking($user_id, $data)
    {
        $client = new GuzzleHttp\Client();
        try {
            $res = $client->put(
                env('PAYMAN_URL') . '/user-accounts/' . $user_id,
                [
                    'headers' => $this->getHeaders(),
                    'body' => json_encode($data)
                ]
            );
            return json_decode($res->getBody());
        } catch (\Exception $e) {
            logger()->error([
                'class PaymanService function updateOrCreateBanking',
                $e->getMessage(),
                'user_id' => $user_id
            ]);
            return response()->json($e->getMessage(), HTTP_BAD_REQUEST)->send();
        }
    }

    /************************************
    * verify a user's banking information
    *************************************/
    public function verifyBanking($user_id, $data)
    {
        $client = new GuzzleHttp\Client();

        try {
            $url = env('PAYMAN_URL')
                    . '/user-accounts/validate?userId='
                    . $user_id
                    . '&amount1='
                    . str_pad($data['amount1'], 4, '0', STR_PAD_LEFT)
                    . '&amount2='
                    . str_pad($data['amount2'], 4, '0', STR_PAD_LEFT);

            $res = $client->get($url, [
                'headers' => $this->getHeaders()
            ]);
            return $result = json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            Log::error('An exception occurred: verify banking');
            return false;
        }
    }

    /************************************
     * Generate metadata information
     *************************************/
    public function getMetadata($payerUserId, $payeeUserId)
    {
        if (!empty($payerUserId)) {
            if ($payerUserId == "Subscription") {
                $customerRole = "Subscription";
            } else {
                $customerRole = User::with('role')->where('id', $payerUserId)->first()->role->name;
                if ($customerRole == "Admin" || $customerRole == "Superadmin") {
                    $customerRole = "Corporate";
                }
            }
        } else {
            $customerRole = "Unknown Payer";
        }
        if (!empty($payeeUserId)) {
            if ($payeeUserId == "Shipping") {
                $storeOwnerRole = $payeeUserId;
            } else {
                $storeOwnerRole = User::with('role')->where('id', $payeeUserId)->first()->role->name;
                if ($storeOwnerRole == "Admin" || $storeOwnerRole == "Superadmin") {
                    $storeOwnerRole = "Corporate";
                }
            }
        } else {
            $storeOwnerRole = "Unknown Payee";
        }
        return ['description' => $storeOwnerRole.' to '.$customerRole];
    }

    /************************************
     * process a credit card transaction
     *************************************/
    /*
    |--------------------------------------------------------------------------
    | Team ID's
    |--------------------------------------------------------------------------
    |    1 = Internal team (wholesale)
    |    2 = Field team (retail)
    */
    public function creditCard($cart, $data, User $seller, User $buyer, $description = null, $affiliatePayouts = null)
    {
        if ($cart->type === 'wholesale') {
            $teamId = 'wholesale';
        } elseif ($seller->role_id === 7 || $seller->role_id === 8) {
            // If the seller is an admin or superadmin use team id 'company'.
            $teamId = 'company';
        } elseif ($seller->role_id === 5) {
            // If the seller is a rep use team id 'rep'.
            $teamId = 'rep';
        }
        if (!isset($teamId)) {
            logger()->error('Invalid Seller Credit Card', [
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'description' => $description,
                'fingerprint' => 'Invalid Seller'
            ]);
            abort(500, 'Invalid Seller');
        }
        if (!isset($description)) {
            $metadata = $this->getMetadata($buyer->id, $seller->id);
            $description = $metadata['description'];
        }
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode(array_filter([
                'payerUserId'    => $buyer->id,
                'payeeUserId'    => $seller->id,
                'teamId'         => $teamId,
                'name'           => $data['payment']['name'],
                'tax'            => $cart['total_tax'],
                'subtotal'       => $cart['subtotal_price'],
                'shipping'       => $cart['total_shipping'],
                'total'          => $cart['total_price'],
                'affiliatePayouts' => $affiliatePayouts,
                'description' => $description,
                'card' => [
                    'number'    => $data['payment']['card_number'],
                    'month'     => $data['payment']['month'],
                    'year'      => $data['payment']['year'],
                    'code'      => $data['payment']['security']
                ],
                'billingAddress' => [
                    'firstName'     => $data['payment']['name'],
                    'line1'         => $data['addresses']['billing']['address_1'],
                    'city'          => $data['addresses']['billing']['city'],
                    'state'         => $data['addresses']['billing']['state'],
                    'postalCode'    => $data['addresses']['billing']['zip'],
                    'email'         => (isset($data['user']['email']) ? $data['user']['email'] : '')
                ],
                'shippingAddress' => [
                    'firstName' => (isset($data['addresses']['shipping']['first_name']) ? $data['addresses']['shipping']['first_name'] : $data['user']['first_name']),
                    'lastName' => (isset($data['addresses']['shipping']["last_name"]) ? $data['addresses']['shipping']['last_name'] : $data['user']['last_name']),
                    'line1'         => $data['addresses']['shipping']['address_1'],
                    "city"          => $data['addresses']['shipping']['city'],
                    "state"         =>$data['addresses']['shipping']['state'],
                    'postalCode'    => $data['addresses']['shipping']['zip']
                ]
            ]))
        ];
        try {
            $url = env('PAYMAN_URL') . '/transactions/sale/credit-card/?showTransaction=true';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            foreach ($body as $key => $value) {
                $response[$key] = $value;
            }

            if ($response['success']) {
                $response['amount'] = $cart['subtotal_price'] + $cart['total_shipping'] + $cart['total_tax'];
            }
            return $response;
        } catch (\Exception $e) {
            Log::error('An exception occurred: credit card method '.$e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }


    /************************************
     * process a credit card transaction
     *************************************/
    /*
    |--------------------------------------------------------------------------
    | Team ID's
    |--------------------------------------------------------------------------
    |    1 = Internal team (wholesale)
    |    2 = Field team (retail)
    */
    public function cash($cart, $data, User $seller, User $buyer, $description = null)
    {
        if ($cart->type === 'wholesale') {
            $teamId = 'wholesale';
        } elseif ($seller->role_id === 7 || $seller->role_id === 8) {
            // If the seller is an admin or superadmin use team id 'company'.
            $teamId = 'company';
        } elseif ($seller->role_id === 5) {
            // If the seller is a rep use team id 'rep'.
            $teamId = 'rep';
        }
        if (!isset($teamId)) {
            logger()->error('Invalid Seller Cash', [
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'description' => $description,
                'fingerprint' => 'Invalid Seller'
            ]);
            abort(500, 'Invalid Seller');
        }
        if (!isset($description)) {
            $metadata = $this->getMetadata($buyer->id, $seller->id);
            $description = $metadata['description'];
        }
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'payerUserId'    => $buyer->id,
                'payeeUserId'    => $seller->id,
                'teamId'         => $teamId,
                'name'           => $data['payment']['name'],
                'tax'            => $cart['total_tax'],
                'subtotal'       => $cart['subtotal_price'],
                'shipping'       => $cart['total_shipping'],
                'total'          => $cart['total_price'],
                'description' => $description,
            ])
        ];

        try {
            $url = env('PAYMAN_URL') . '/transactions/sale/cash/?showTransaction=true';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            foreach ($body as $key => $value) {
                $response[$key] = $value;
            }

            if ($response['success']) {
                $response['amount'] = $cart['subtotal_price'] + $cart['total_shipping'] + $cart['total_tax'];
            }
            return $response;
        } catch (\Exception $e) {
            Log::error('An exception occurred: cash method '.$e->getMessage());
            return ['success' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     *  For use with payment object
     *  $type is 'sub' or 'sale'
     *  $teamId in ('company', 'wholesale', 'rep')
     */
    public function makePayment($type, $buyerId, $sellerId, $teamId, $payment, $amount, $tax, $shipping, $description, $address)
    {
        $client = new GuzzleHttp\Client();
        $requestBody = [
            'payerUserId'    => $buyerId,
            'payeeUserId'    => $sellerId,
            'teamId'         => $teamId,
            'amount'       => $amount,
            'tax'            => $tax,
            'shipping'       => $shipping,
            'description' => $description
        ];
        if ($address != null) {
            $requestBody['billingAddress'] = [
                'line1' => isset($address['address_1']) ? $address['address_1'] : null,
                'line2' => isset($address['address_2']) ? $address['address_2'] : null,
                'city' => isset($address['city']) ? $address['city'] : null,
                'state' => isset($address['state']) ? $address['state'] : null,
                'postalCode' => isset($address['zip']) ? $address['zip'] : null
            ];
        }

        switch ($payment['type']) {
            case 'card':
                $requestBody['transactionType'] = 'credit-card-' . $type;
                $requestBody['card'] = $payment['card'];
                $requestBody['accountHolder'] = $payment['card']['name'];
                break;
            case 'e-check':
                $requestBody['transactionType'] = 'check-' . $type;
                $requestBody['bankAccount'] = $payment['account'];
                break;
            default:
                Log::error('makePayment payment type incorrect', ['payment' => $payment]);
                return ['success' => false, 'description' => 'Unexpected error'];
        }

        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode($requestBody)
        ];

        try {
            $url = env('PAYMAN_URL') . '/transactions';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            foreach ($body as $key => $value) {
                $response[$key] = $value;
            }

            return $response;
        } catch (\Exception $e) {
            Log::error($e);
            return ['success' => false, 'description' => $e->getMessage(), 'resultCode' => 99, 'result' => 'Unexpected error'];
        }
    }

    /************************************
     * Create Card Token
     *************************************/
    public function cardToken($data, $userId)
    {
        $user_email = User::where('id', $userId)->first()->email;
        if (!isset($data['payment']) || $data['payment'] == null) {
            return 'No card info';
        }
        $client = new GuzzleHttp\Client();

        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'card' => [
                    'name'   => $data['payment']['name'],
                    'number' => $data['payment']['card_number'],
                    'month'  => $data['payment']['month'],
                    'year'   => $data['payment']['year'],
                    'code'   => $data['payment']['code'],
                ],
                'payerId' => $userId,
                'address' => [
                    'first_name'    =>$data['payment']['name'],
                    'line1'         => $data['addresses']['billing']['address_1'],
                    'line2'         => (isset($data['addresses']['billing']['address_2']) ? $data['addresses']['billing']['address_2'] : null),
                    'city'          => $data['addresses']['billing']['city'],
                    'state'         => $data['addresses']['billing']['state'],
                    'postalCode'    => $data['addresses']['billing']['zip'],
                    'email'         => (isset($user_email) && $user_email != null ? $user_email : '')
                ]
            ])
        ];
        if (isset($data['addresses'])) {
            $params += [
                'address' => [
                    'line1'      => $data['addresses']['billing']['address_1'],
                    'postalCode' => $data['addresses']['billing']['zip'],
                ]
            ];
        }

        try {
            $url = env('PAYMAN_URL') . '/tokenization/card/?teamId=1';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            if (!$body['success']) {
                throw new APIResponseException(422, $body['description'], $body['description'], null);
            }
            return $body;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred for get token: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function cardTokenV2($card, $address, $userId)
    {
        $client = new GuzzleHttp\Client();

        $requestBody = [
            'card' => $card,
            'payerId' => $userId
        ];
        if ($address != null) {
            $requestBody['address'] = [
                'name'          => (isset($address['name']) ? $address['name'] : null),
                'line1'         => (isset($address['address_1']) ? $address['address_1'] : null),
                'line2'         => (isset($address['address_2']) ? $address['address_2'] : null),
                'city'          => (isset($address['city']) ? $address['city'] : null),
                'state'         => (isset($address['state']) ? $address['state'] : null),
                'postalCode'    => (isset($address['zip']) ? $address['zip'] : null),
                'email'         => (isset($address['email']) ? $address['email'] : null)
            ];
        }
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode($requestBody)
        ];

        try {
            $url = env('PAYMAN_URL') . '/tokenization/card/?teamId=company';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            if (!$body['success']) {
                throw new APIResponseException(422, $body['description'], $body['description'], null);
            }
            return $body;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred for get token: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /************************************
     * Subscription Payment Processing
     *************************************/

    public function subCreditCard($data, $token = null, $user_name = null)
    {
        $requestBody = [
            'payeeUserId' => config('site.apex_user_id'),
            'payerUserId' => auth()->id(), // leaving this incase of backwards compat
            'payerId'     => auth()->id(),
            'total'       => $data['subtotal_price'] + $data['total_tax'],
            'subtotal'    => $data['subtotal_price'],
            'tax'         => $data['total_tax'],
            'teamId'      => 1,
            'description' => "Subscription"
        ];
        if (isset($token)) {
            $requestBody += [
            'cardToken' => $token->token,
            'name' => $user_name,
            'gatewayCustomerId' => (isset($token->gateway_customer_id) ? $token->gateway_customer_id : null)
            ];
        } else {
            $requestBody += [
                'card' => [
                    'name'      => $data['payment']['name'],
                    'number'    => $data['payment']['card_number'],
                    'month'     => $data['payment']['month'],
                    'year'      => $data['payment']['year'],
                    'code'      => $data['payment']['security']
                ],
                'billingAddress' => [
                    'line1'         => $data['addresses']['billing']['address_1'],
                    'postalCode'    => $data['addresses']['billing']['zip']
                ]
            ];
        }
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode($requestBody)
        ];
        try {
            $url = env('PAYMAN_URL') . '/transactions/sub/credit-card/?showTransaction=true';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            return $body;
        } catch (\Exception $e) {
            Log::error('An exception occurred: credit card method ' . $e->getMessage());
            return ['success' => false, 'description' => 'Server Error'];
        }
    }

    public function subEcheck($user_id, $price)
    {
        $bankInfo = $this->getBanking($user_id);
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'payerUserId' => $user_id,//this need changed,
                'payeeUserId' => $user_id,
                'subtotal' => $price, //this needs changed
                'tax' => 0,
                'teamId' => 2,
                'accountName' => $bankInfo['name'],
                'accountNumber' => $bankInfo['number'],
                'routingNumber' => $bankInfo['routing'],
            ])
        ];
        try {
            $url = env('PAYMAN_URL') . '/transactions/sub/e-check/?showTransaction=true';
            $res = $client->post($url, $params);
            return json_decode($res->getBody(), 1);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            Log::error('An exception occurred: credit card method ' .$e->getMessage());
            return false;
        }
    }
    public function payManPagination($data, $request)
    {
        return new LengthAwarePaginator(
            $data['data'],
            $data['total'],
            $request['per_page'],
            $request['page']
        );
    }

    public function processShipping($shippingRate, $payment, $userId)
    {
        $settingsService = new SettingsService;
        $metadata = $this->getMetadata($userId, 'Shipping');
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'payerUserId'    => $userId,
                'payeeUserId'    => $settingsService->getGlobal('shipping_team_id', 'value'),
                'teamId'         => $settingsService->getGlobal('shipping_team_id', 'value'), //controlpad or company
                'name'           => $payment['name'],
                'tax'            => 0,
                'total'       => $shippingRate['total_price'],
                'description'       => $metadata['description'],
                'card' => [
                    'number'    => $payment['card_number'],
                    'month'     => $payment['month'],
                    'year'      => $payment['year'],
                    'code'      => $payment['security']
                ],
                'billingAddress' => [
                    'line1'         => $payment['address_1'],
                    'postalCode'    => $payment['zip']
                ]
            ])
        ];

        try {
            $url = env('PAYMAN_URL') . '/transactions/shipping/credit-card/?showTransaction=true';
            $response = $client->post($url, $params);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['error' => $e->getMessage(), 'success' => false];
        }

        $response = json_decode($response->getBody(), true);
        if ($response['success'] === false) {
            $response['error'] = 'ERROR';
        }
        return $response;
    }



    public function subAccounts($user, $application)
    {
        $client = new GuzzleHttp\Client();
        $dob = Carbon::createFromFormat('Y-m-d', $application['owner']['dob'])->toDateString();
        if ($application['provider'] === 'splash_option_1') {
            $application['owner']['address'] = $application['address'];
            $application['ein'] = $application['owner']['ssn'];
            $application['established'] = Carbon::yesterday()->toDateString('Y-m-d');
        }
        $website = sprintf(env('REP_URL'), $user->public_id);
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'teamId' => 'rep',
                'userId' => $user->id,
                'business' => [
                    'merchantCategoryCode' => $application['merchantCategoryCode'],
                    'name' => $application['name'],
                    'phone' => $application['phone'],
                    'established' => $application['established'],
                    'ein' => $application['ein'],
                    'email' => $user->email,
                    'type' => $application['type'],
                    'website' => $website,
                    'address' => [
                        'line1' => $application['address']['address_1'],
                        'city' => $application['address']['city'],
                        'state' => $application['address']['state'],
                        'postalCode' => $application['address']['zip'],
                        'countryCode' => "USA",
                    ],
                    'account' => [
                        'routing' => $application['account']['routing'],
                        'number' => $application['account']['number'],
                        'type' => $application['account']['type'],
                    ],
                    'owner' => [
                        'firstName' => $application['owner']['first_name'],
                        'lastName' => $application['owner']['last_name'],
                        'dob' => str_replace(['-'], '', $dob),
                        'phone' => $application['owner']['phone'],
                        'email' => $application['owner']['email'],
                        'ssn' => $application['owner']['ssn'],
                        'ownership' => (isset($application['owner']['ownership'])) ? $application['owner']['ownership'] : 100,
                        'address' => [
                            'line1' => $application['owner']['address']['address_1'],
                            'city' => $application['owner']['address']['city'],
                            'state' => $application['owner']['address']['state'],
                            'postalCode' => $application['owner']['address']['zip'],
                            'countryCode' => "USA",
                        ]
                    ]
                ]
            ], JSON_UNESCAPED_SLASHES)
        ];
        try {
            $url = env('PAYMAN_URL') . '/sub-accounts';
            $res = $client->post($url, $params);
            $body = json_decode($res->getBody(), 1);
            if ($body['success'] === false) {
                // A log for now to sort out why a user might not succeed to allow us to decide how to parse the errors as user friendly
                Log::error('Failed to create sub account: (' . $body['statusCode'] . ') ' . $body['description']);
                return [
                    'error' => true,
                    'status' => 422,
                    'body' => $body
                ];
            }
            return ['status' => 200, 'error' => false, 'body' => $body];
        } catch (GuzzleHttp\Exception\ClientException $e) {
            Log::error($e);
            $response = $e->getResponse();
            return [
                'error' => true,
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), 1)
            ];
        }
    }

    public function subAccountExists($user_id)
    {
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
        ];
        try {
            $url = env('PAYMAN_URL') . '/transactions/select-gateway/?teamId=rep&payeeUserId=' . $user_id;
            Log::info($url);
            $res = $client->get($url, $params);
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            Log::error(['error' => $e->getMessage()]);
            return false;
        }
    }

    // function for refunding money based on a passed order object
    public function refundOrder($order, $refundType = 'refund', $fbc = null)
    {
        $client = new GuzzleHttp\Client();
        try {
            $url = env('PAYMAN_URL') . "/transactions/" . $order->transaction_id . "/" . $refundType;
            if ($fbc !== null) {
                $request = [
                    "headers" => $this->getHeaders(),
                    "body" => json_encode([
                        "total" => $order->total_price,
                        "tax" => $order->total_tax,
                        "affiliatePayouts" =>$fbc
                    ])
                ];
            } else {
                $request = [
                    "headers" => $this->getHeaders(),
                    "body" => json_encode([
                        "total" => $order->total_price,
                        "tax" => $order->total_tax
                    ])
                ];
            }
            $res = $client->post($url, $request);
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            logger()->warning('Payman: error refunding order', [
                'message' => $e->getMessage(),
                'fingerprint' => 'PaymanService refundOrder',
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /********************************************
    * get transactions id information for orders
    *********************************************/
    public function getTransactionsId($transaction_id)
    {
        return $this->get('reports/transactions/' . $transaction_id);
    }

    /********************************************
    * generate a new invitation through PayQuicker api
    *********************************************/
    public function generatePayQuickerInvite($user = null)
    {
        // if no user is passed lets use our logged in user
        if ($user == null) {
            $user = auth()->user();
        }

        // build our request
        $request = [
            'teamId' => 'rep',
            'userId' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email
        ];

        // build client and parameters
        $client = new GuzzleHttp\Client();
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode($request)
        ];
        // perform request
        try {
            $url = env('PAYMAN_URL')
                . '/payment-providers/payquicker/invitation';
            $res = $client->post($url, $params);
            return json_decode($res->getBody(), 1);
        } catch (\Exception $e) {
            Log::error(['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
