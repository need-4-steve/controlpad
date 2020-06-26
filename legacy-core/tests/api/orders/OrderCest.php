<?php
namespace api;

use ApiTester;
use App\Models\Order;
use \Step\Api\UserAuth;
use \Carbon\Carbon;

class OrderCest
{
    public function _before(UserAuth $I)
    {
        $this->order = Order::first();
        $this->repOrder = Order::where('store_owner_user_id', 106)->first();

        $this->request =[
            "column"      => "id",
            "end_date"    => Carbon::now()->toDateTimeString(),
            "order"       => "asc",
            "page"        => "1",
            "per_page"    => "15",
            "search_term" => "",
            "start_date"  => Carbon::now()->subDay(60)->toDateTimeString()
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetIndexAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/orders', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->loginAsRep();

        $customerId = $I->haveRecord(
            'users',
            [
                'first_name' => 'Test',
                'last_name'  => 'Customer',
                'email'      => 'testcustomer@example.com',
                'role_id'    => 3
            ]
        );

        $I->haveRecord(
            'orders',
            [
                'customer_id'         => $customerId,
                'store_owner_user_id' => 106,
                'receipt_id'          => '5807dec1e5a43',
                'type_id'             => 1,
                'total_price'         => 75.00,
                'subtotal_price'      => 70.00,
                'total_tax'           => 5.00,
                'total_shipping'      => 5.00,
                'total_discount'      => 0,
                'paid_at'             => null,
                'cash'                => random_int(0, 1),
                'source'              => 'ios',
                'transaction_id'      => '1bmOVMmCgq8APY',
                'created_at'          => Carbon::now()->subDay(10)->toDateTimeString()
            ]
        );
        $I->sendGET('/api/v1/orders', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'store_owner_user_id' => 106
        ]);
    }

    public function tryGetShow(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/orders/show/'.$this->order->receipt_id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'receipt_id' => $this->order->receipt_id
        ]);
    }

    public function tryGetShowAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendGET('/api/v1/orders/show/'.$this->order->receipt_id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'receipt_id' => $this->order->receipt_id
        ]);
    }

    public function tryGetOrderTypes(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGet('/api/v1/orders/order-types');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => 1
        ]);
    }

    public function tryShowOrderTotalsByDate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/orders/by-date');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }
}
