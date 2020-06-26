<?php namespace App\Services\PayMan;

use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\PromotionRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Models\Order;
use App\Services\PayMan\PayManCommon;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\UserSetting;

class EwalletService extends PayManCommon
{
    use CommonCrudTrait;

    public function __construct(
        OrderRepository $orderRepo,
        AuthRepository $authRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->authRepo = $authRepo;
        $this->client = new \GuzzleHttp\Client();
        $this->globalSettings = app('globalSettings');
    }

    public function dashboardReport($userId, $teamId)
    {
        return $this->getWithQuery('reports/e-wallet-dashboard', ['query' => ['userId' => $userId, 'teamId' => $teamId]]);
    }

    public function salesTax($request)
    {
        return $this->getWithQuery('reports/sales-tax', ['query' => $request]);
    }

    public function processingFees($request)
    {
        return $this->getWithQuery('reports/processing-fees', ['query' => $request]);
    }

    public function openSales()
    {
        return $this->get('reports/open-sales/?userId=' . auth()->user()->id);
    }

    public function openTax()
    {
        return $this->get('reports/open-tax/?userId=' . auth()->user()->id);
    }

    public function taxOwedByUser($request)
    {
        $query = [
          'page'  => isset($request['page']) ? $request['page'] : 1,
          'count' => isset($request['per_page']) ? $request['per_page'] : 15,
          'sort' => isset($request['sort']) ? $request['sort'] : '-taxOwed', // taxOwed, userId are valid options, use negative for descending
          'min' => isset($request['min']) ? $request['min'] : '0.00'
        ];
        $response = $this->getWithQuery('reports/tax-user-balances', ['query' => $query]);
      //pull user names for IDs
        $userIds = array_map(function ($a) {
            return $a['userId'];
        }, $response['data']);
        $users = DB::table('users')->select('id', 'first_name', 'last_name')->whereIn('id', $userIds)->get()->keyBy('id');
      // Append user names to payman response data
        foreach ($response['data'] as $key => $item) {
            $user = $users->get($item['userId']);
            if (isset($user)) {
                $response['data'][$key]['name'] = $user->first_name.' '.$user->last_name;
            }
        }
        return $this->convertPagination($response);
    }

    public function withdraw($total, $source)
    {
        $data = [
            'payerUserId' => $this->authRepo->getOwnerId(),
            'payeeUserId' => $this->authRepo->getOwnerId(),
            'total' => $total,
            'teamId' => $source
        ];
        return $this->post('transactions/withdraw/e-wallet', $data);
    }

    public function transactions($request)
    {
        return $this->getWithQuery('reports/transactions', ['query' => $request]);
    }

    public function transaction($id)
    {
        $data = $this->get('reports/transactions/' . $id);
        if (isset($data['entries'])) {
            foreach ($data['entries'] as $key => $entry) {
                if ($entry['type'] === 'merchant') {
                    unset($data['entries'][$key]);
                    array_unshift($data['entries'], $entry);
                }
            }
        }
        $userTimeZone = $this->getUserTimeZone(auth()->id());
        $data['date'] = Carbon::createFromFormat('Y-m-d H:i:s', substr($data['createdAt'], 0, -2), 'UTC')->setTimezone($userTimeZone)->toDateTimeString();
        return $data;
    }

    public function invoiceDetails($id)
    {
        $order = $this->orderRepo->find($id, [
            'customer',
            'customer.phones',
            'orderType',
            'lines',
            'lines.item',
            'shippingAddress'
        ]);
        $order->totalBundles = $order->totalBundles();
        $order->totalProducts = $order->totalProducts();
        $order->payment = $this->transaction($id);
        return $order;
    }

    public function myPayment($request)
    {
        return $this->getWithQuery('reports/my-payments', ['query' => $request]);
    }

    public function payReturn($request)
    {
        $query = [
                'authUserId' => $request['authUser'],
                'amount' => $request['amount']
        ];
        $refund = $this->post('transactions/'.$request['id'].'/refund', $query);
        return $refund;
    }

    public function ledger($request)
    {
        $request['source'] = 'Rep';
        $request['teamId'] = $request['source'];
        $userTimeZone = $this->getUserTimeZone($this->authRepo->getOwnerId());
        $ledger = $this->getWithQuery('/reports/balance-ledger', ['query' => $request]);
        $transactionIDs = [];
        foreach ($ledger['data'] as $key => $transaction) {
            if (isset($transaction['transactionId'])) {
                $transactionIDs[] = $transaction['transactionId'];
            }
            $ledger['data'][$key]['date'] = Carbon::createFromFormat('Y-m-d H:i:s', substr($transaction['date'], 0, -2), 'UTC')->setTimezone($userTimeZone)->toDateTimeString();
        }
        if (count($transactionIDs) > 0) {
            $orderMap = Order::select('orders.receipt_id', 'orders.subtotal_price as subtotal', 'orders.total_discount as discount', 'ot.name as type', 'orders.transaction_id', 'orders.created_at')
            ->join('order_types as ot', 'ot.id', '=', 'orders.type_id')
            ->whereIn('orders.transaction_id', $transactionIDs)
            ->get()->keyBy('transaction_id')->all();

            foreach ($ledger['data'] as $key => $transaction) {
                if (isset($transaction['transactionId']) && array_key_exists($transaction['transactionId'], $orderMap)) {
                    $ledger['data'][$key]['order'] = $orderMap[$transaction['transactionId']];
                }
            }
        }
        return $ledger;
    }

    public function getParams($request)
    {
        if (isset($request['user_id']) && $this->authRepo->isAdmin()) {
           // Allows admins to select user
            $userId = $request['user_id'];
        } else {
            $userId = $this->authRepo->getOwnerId();
        }

        $userTimeZone = $this->getUserTimeZone($this->authRepo->getOwnerId());
        if ($this->authRepo->isOwnerAdmin()) {
            $userType = 'Company';
        } else {
            $userType = 'Rep';
        }

        $query = [
            'page'  => isset($request['page']) ? $request['page'] : 1,
            'count' => isset($request['per_page']) ? $request['per_page'] : 15,
            'payeeUserId' => $this->authRepo->getOwnerId(),
            'userId' => $userId,
            'source' => isset($request['source']) ? $request['source'] : $userType
        ];
        if (isset($request['start_date'])) {
            $query['startDate'] = Carbon::createFromFormat('Y-m-d', $request['start_date'], $userTimeZone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        } else {
            $query['startDate'] = Carbon::createFromFormat('Y-m-d', date('Y-m-d'), $userTimeZone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        }
        if (isset($request['end_date'])) {
            $query['endDate'] = Carbon::createFromFormat('Y-m-d', $request['end_date'], $userTimeZone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        } else {
            $query['endDate'] = Carbon::createFromFormat('Y-m-d', date('Y-m-d'), $userTimeZone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        }
        return $query;
    }

    private function getUserTimeZone($userId)
    {
        $timeZone = UserSetting::where('user_id', $userId)->first();
        if ($timeZone == null) {
            $timeZone = 'UTC';
        } else {
            $timeZone = $timeZone->timezone;
        }

        return $timeZone;
    }

    public function pagination($data, $request)
    {
        return new LengthAwarePaginator(
            $data['data'],
            $data['total'],
            $request['count'],
            $request['page']
        );
    }

    public function payTaxesByCreditCard($request)
    {
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode(array_filter([
                'payerUserId'       => $this->authRepo->getOwnerId(),
                'total'             => $request['total'],
                'teamId'            => 'company',
                'card' => [
                    'number'        => $request['payment']['card_number'],
                    'month'         => $request['payment']['month'],
                    'year'          => $request['payment']['year'],
                    'code'          => $request['payment']['security'],
                    'name'          => $request['payment']['name'],
                ],
                'billingAddress' => [
                    'line1'         => $request['addresses']['billing']['address_1'],
                    'line2'         => isset($request['addresses']['billing']['address_2']) ? $request['addresses']['billing']['address_2'] : null,
                    'city'          => $request['addresses']['billing']['city'],
                    'state'         => $request['addresses']['billing']['state'],
                    'postalCode'    => $request['addresses']['billing']['zip'],
                ]
            ]))
        ];

        try {
            $url = env('PAYMAN_URL') . '/transactions/tax-payment/card';
            $response = $this->client->post($url, $params);
            return json_decode($response->getBody(), 1);
        } catch (\Exception $e) {
            logger()->error([
                'class EwalletService function payTaxesByCreditCard',
                $e->getMessage(),
                'user_id' => $this->authRepo->getOwnerId()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400)->send();
        }
    }

    public function payTaxesByEcheck($request)
    {
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'payerUserId'       => $this->authRepo->getOwnerId(),
                'total'             => $request['total'],
                'teamId'            => 'company',
                'accountName'       => $request['account_name'],
                'accountNumber'     => $request['account_number'],
                'routingNumber'     => $request['routing_number'],
            ])
        ];

        try {
            $url = env('PAYMAN_URL') . '/transactions/tax-payment/e-check';
            $response = $this->client->post($url, $params);
            return json_decode($response->getBody(), 1);
        } catch (\Exception $e) {
            logger()->error([
                'class EwalletService function payTaxesByEcheck',
                $e->getMessage(),
                'user_id' => $this->authRepo->getOwnerId()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400)->send();
        }
    }

    public function payTaxesByEwallet($request)
    {
        $params = [
            'headers' => $this->getHeaders(),
            'body' => json_encode([
                'payerUserId'       => $this->authRepo->getOwnerId(),
                'total'             => $request['total'],
                'teamId'            => 'rep',
            ])
        ];
        try {
            $url = env('PAYMAN_URL') . '/transactions/tax-payment/e-wallet';
            $response = $this->client->post($url, $params);
            return json_decode($response->getBody(), 1);
        } catch (\Exception $e) {
            logger()->error([
                'class EwalletService function payTaxesByEwallet',
                $e->getMessage(),
                'user_id' => $this->authRepo->getOwnerId()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400)->send();
        }
    }

    public function getCashTaxLedger($request, $csv = false)
    {
        $ledger = $this->getWithQuery('reports/cash-tax-ledger', ['query' => $request]);
        DB::beginTransaction();
        foreach ($ledger['data'] as $key => $transaction) {
            if ($csv) { // only for the csv download
                $order = Order::where('transaction_id', $transaction['transactionId'])
                    ->select(
                        'transaction_id',
                        'receipt_id',
                        'subtotal_price',
                        'total_tax',
                        'total_shipping',
                        'total_price',
                        'addresses.name as customer_name',
                        'address_1',
                        'address_2',
                        'city',
                        'state',
                        'zip',
                        'customer_id',
                        'order_types.name as order_type'
                    )
                    ->join('addresses', function ($join) {
                        $join->on('orders.id', '=', 'addresses.addressable_id')
                        ->where('addresses.addressable_type', '=', Order::class)
                        ->where('label', 'Shipping');
                    })
                    ->join('order_types', 'orders.type_id', 'order_types.id')
                    ->first();
                if ($order) {
                    $ledger['data'][$key]['receipt_id'] = $order->receipt_id;
                    $ledger['data'][$key]['order'] = $order;
                }
            } elseif (isset($transaction['transactionId'])) {
                $ledger['data'][$key]['receipt_id'] = Order::where('transaction_id', $transaction['transactionId'])
                    ->pluck('receipt_id')
                    ->first();
            }
        }
        DB::commit();
        return $ledger;
    }
}
