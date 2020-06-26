<?php
namespace order;

use ApiTester;
use App\Models\Order;
use \Step\Api\UserAuth;
use \Carbon\Carbon;

class OrderFailuresCest
{
    public function _before(UserAuth $I)
    {
        $this->order = Order::first();

        $this->request =[
            "column" => "id",
            "end_date" => "2016-09-20T06:00:00.000Z",
            "order" => "asc",
            "page" => "1",
            "per_page" => "15",
            "search_term" => "",
            "start_date" => "2016-06-22T06:00:00.000Z"
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetIndexNotLoggedIn(UserAuth $I)
    {
        $I->sendGET('/api/v1/orders', $this->request);
        $I->seeResponseCodeIs(401);
    }

    public function tryGetShowNotLoggedIn(UserAuth $I)
    {
        $I->sendGET('/api/v1/orders/show/'.$this->order->receipt_id);
        $I->seeResponseCodeIs(401);
    }

    public function tryShowOrderTotalsByDateNotLoggedIn(UserAuth $I)
    {
        $I->sendGET('/api/v1/orders/by-date');
        $I->seeResponseCodeIs(401);
    }
}
