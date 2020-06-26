<?php

use App\Models\Inventory;
use App\Models\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;
use Faker\Factory;

class InventoryFailureTest extends TestCase
{
    public function testUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);

        $request = [];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testQuantity()
    {
        $request = [
            'user_id' => 0,
            'quantity' => pi()
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);

        $request = [
            'user_id' => 0,
            'quantity' => 'error'
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testInventoryPrice()
    {
        $request = [
            'user_id' => 0,
            'inventory_price' => 1000000
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);

        $request = [
            'user_id' => 0,
            'inventory_price' => .001
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);

        $request = [
            'user_id' => 0,
            'inventory_price' => 'error'
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testDisable()
    {
        $request = [
            'user_id' => 0,
            'disable' => 'error'
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/0', $request);
        $response->assertResponseStatus(422);
    }
}
