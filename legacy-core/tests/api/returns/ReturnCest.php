<?php
namespace returns;

use \ApiTester;
use \Step\Api\UserAuth;
use \App\Models\Returnline;
use \App\Models\ReturnModel;
use \App\Models\ReturnReason;
use \App\Models\ReturnStatus;
use \App\Models\Orderline;
use \App\Models\Order;

class ReturnCest
{
    public $order;

    public $returnItem;

    public function _before(UserAuth $I)
    {
        $this->order = Order::where('status', 'fulfilled')->first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToGetAll(UserAuth $I)
    {
        $I->loginAsSuperadmin();
        $request = [
            'order'       => 'ASC',
            'column'      => 'order_id',
            'per_page'    => '15',
            'search_term' => '',
            'start_date'  => \Carbon\Carbon::now()->subDays(10)->toDateTimeString(),
            'end_date'    =>\Carbon\Carbon::now()->toDateTimeString(),
            'status'       => 'all'
        ];

        $I->haveRecord('returns', [
            'user_id'           => 106,
            'order_id'          => $this->order->id,
            'initiator_user_id' => 109,
            'return_status_id'  => 1,
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        ]);

        $I->sendAjaxRequest('GET', '/api/v1/returns', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToPostRequest(UserAuth $I)
    {
        $I->loginAsRep();
        $order = \App\Models\Order::with('lines')->where(['store_owner_user_id' => 106, 'status' => 'fulfilled'])->first();
        $request = [
            'return_items' => [
                0 => [
                    'order_id'        => $order->id,
                    'comments'        => 'Damaged',
                    'reason_id'       => 1,
                    'return_quantity' => 1,
                    'customer'        => 106,
                    'orderline_id'     => $order->lines[0]->id
                ]
            ]
        ];
        $I->sendAjaxRequest('POST', '/api/v1/returns/request', $request);
        $I->seeResponseCodeIs(200);

        $this->returnItem = (array) json_decode($I->grabResponse());

        $I->seeRecord('returns', [
            'order_id'         => $order->id,
            'return_status_id' => 1,
        ]);
    }

    /**
     * @depends tryToPostRequest
     */
    public function tryToPatchUpdate(UserAuth $I)
    {
        $I->loginAsSuperadmin();
        $I->haveRecord('returns', [
            'id' =>             53,
            'user_id'           => 106,
            'order_id'          => $this->order->id,
            'initiator_user_id' => 109,
            'return_status_id'  => 1,
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        ]);

        $request = [
            'return_status_id' => 3,
        ];
        $I->sendAjaxRequest('PATCH', '/api/v1/returns/update/53', $request);

        $I->seeResponseCodeIs(200);
        $I->seeRecord('returns', [
            'id'               => 53,
            'return_status_id' => 3,
        ]);
    }
}
