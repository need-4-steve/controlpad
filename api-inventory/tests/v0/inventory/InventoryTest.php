<?php

use App\Models\Inventory;
use App\Models\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class InventoryTest extends TestCase
{
    public function testCreateBasic()
    {
        $inventory = factory(Inventory::class, 'withRelations')->make([
            'user_id' => 106,
            'owner_id' => 106,
        ]);
        $inventory->quantity = rand(10, 100);
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/'.$inventory->item_id, $inventory->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson([
            'user_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disable' => false,
            'inventory_price' => null,
        ]);
        $this->seeInDatabase('inventories', [
            'user_id' => 106,
            'owner_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disabled_at' => null
        ]);
    }

    public function testCreatePrice()
    {
        $inventory = factory(Inventory::class, 'withRelations')->make([
            'user_id' => 106,
            'owner_id' => 106,
        ]);
        $inventory->quantity = rand(10, 100);
        $inventory->inventory_price = money_format("%!n", (rand(100, 2000) / 100));
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/'.$inventory->item_id, $inventory->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson([
            'user_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disable' => false,
            'inventory_price' => $inventory->inventory_price,
        ]);
        $this->seeInDatabase('inventories', [
            'user_id' => 106,
            'owner_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disabled_at' => null,
        ]);
        $content = json_decode($response->response->getContent());
        $this->seeInDatabase('prices', [
            'price_type_id' => 4,
            'priceable_type' => 'App\Models\Inventory',
            'priceable_id' => $content->id,
            'price' => $inventory['inventory_price'],
        ]);
    }

    public function testUpdateBasic()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create([
            'user_id' => 106,
            'owner_id' => 106,
            'quantity_available' => 0,
        ]);
        $inventory->quantity = rand(10, 100);
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/'.$inventory->item_id, $inventory->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson([
            'user_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disable' => false,
            'inventory_price' => null,
        ]);
        $this->seeInDatabase('inventories', [
            'user_id' => 106,
            'owner_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity,
            'disabled_at' => null
        ]);
    }

    public function testUpdateNegative()
    {
        $positiveQuanity = rand(100, 200);
        $inventory = factory(Inventory::class, 'withRelations')->create([
            'user_id' => 106,
            'owner_id' => 106,
            'quantity_available' => $positiveQuanity,
        ]);
        $negativeQuantity = -rand(10, 100);
        $inventory->quantity = $negativeQuantity;
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/'.$inventory->item_id, $inventory->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson([
            'user_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $positiveQuanity + $negativeQuantity,
            'disable' => false,
            'inventory_price' => null,
        ]);
        $this->seeInDatabase('inventories', [
            'user_id' => 106,
            'owner_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $positiveQuanity + $negativeQuantity,
            'disabled_at' => null
        ]);
    }

    public function testUpdatePrice()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create([
            'user_id' => 106,
            'owner_id' => 106,
            'quantity_available' => 0,
        ]);
        $inventory->inventory_price = money_format("%!n", rand(100, 1000) / 100);
        $response = $this->basicRequest('PATCH', '/api/v0/inventory/'.$inventory->item_id, $inventory->toArray());
        $response->assertResponseStatus(200);
        $response->seeJson([
            'user_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity_available,
            'disable' => false,
            'inventory_price' => $inventory->inventory_price,
        ]);
        $this->seeInDatabase('inventories', [
            'user_id' => 106,
            'owner_id' => 106,
            'item_id' => $inventory->item_id,
            'quantity_available' => $inventory->quantity_available,
            'disabled_at' => null
        ]);
        $content = json_decode($response->response->getContent());
        $this->seeInDatabase('prices', [
            'price_type_id' => 4,
            'priceable_type' => 'App\Models\Inventory',
            'priceable_id' => $content->id,
            'price' => $inventory->inventory_price,
        ]);
    }
}
