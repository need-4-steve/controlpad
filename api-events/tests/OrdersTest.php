<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Order;
use App\User;

class OrdersTest extends TestCase
{
    private $orderIndexJsonStructure = [
        'current_page',
        'data',
        'data' => [
            '*' => [
                'id',
                'buyer_id',
                "id",
                "receipt_id",
                "type_id",
                "type_description",
                "buyer_first_name",
                "buyer_last_name",
                "seller_id",
                "buyer_id",
                "total_price",
                "subtotal_price",
                "total_discount",
                "total_shipping",
                "total_tax",
                "cash",
                "source",
                "paid_at",
                "status",
                "created_at",
            ]
        ],
        'first_page_url',
        'next_page_url',
        'prev_page_url',
        'path',
        'from',
        'to'
    ];

    private $singleOrderJsonStructure = [
        'id',
        'buyer_id',
        "id",
        "receipt_id",
        "type_id",
        "type_description",
        "buyer_first_name",
        "buyer_last_name",
        "seller_id",
        "buyer_id",
        "total_price",
        "subtotal_price",
        "total_discount",
        "total_shipping",
        "total_tax",
        "cash",
        "source",
        "paid_at",
        "status",
        "created_at",
    ];
    /**
     * A basic test example. dd(get_class_methods($response)) is your documentation
     *
     * @return void
     */
    public function testGetOrders()
    {
        $response = $this->basicRequest('GET', '/api/v0/orders');
        $response->seeJsonStructure($this->orderIndexJsonStructure);
        $response->seeJson(['current_page' => 1]);
        $response->seeStatusCode(200);
        // dd(get_class_methods($response));
    }

    public function testGetOrder()
    {
        $order = Order::first();
        $response = $this->basicRequest('GET', '/api/v0/orders/'.$order->id);
        $response->seeJson([
            'id' => $order->id,
            'receipt_id' => $order->receipt_id,
            'type_id' => $order->type_id,
            'seller_id' => $order->store_owner_user_id,
            'buyer_id' => $order->customer_id,
            'total_price' => $order->total_price
        ]);
        $response->seeJsonStructure($this->singleOrderJsonStructure);
        $response->seeStatusCode(200);
    }

    public function testGetOrdersBySeller()
    {
        $user = User::where('id', 1)->first();
        $response = $this->basicRequest('GET', '/api/v0/sellers/'.$user->id.'/orders');
        $response->seeJsonStructure($this->orderIndexJsonStructure);
        $response->seeStatusCode(200);
    }

    public function testGetOrdersByBuyer()
    {
        $user = User::where('seller_type_id', 2)->first();
        $response = $this->basicRequest('GET', '/api/v0/buyers/'.$user->id.'/orders');
        $response->seeJsonStructure($this->orderIndexJsonStructure);
        $response->seeStatusCode(200);
    }
}
