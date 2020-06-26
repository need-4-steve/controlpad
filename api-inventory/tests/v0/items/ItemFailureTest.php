<?php

use App\Models\Inventory;
use App\Models\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;
use Faker\Factory;

class ItemFailureTest extends TestCase
{
    public function testFindAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/items/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindAvailableZeroInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $inventory->load('item');
        $item = $inventory->item;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/items/'.$item->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/items/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testCreateVariantId()
    {
        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->variant_id = 0;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateSku()
    {
        $exist = factory(Item::class, 'withRelations')->create();
        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $exist->manufacturer_sku;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreatePrices()
    {
        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->wholesale_price = -1;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->retail_price = -1;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->premium_price = -1;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateOption()
    {
        $faker = Factory::create();
        $request = factory(Item::class, 'withRelations')->create();
        $request->sku = $faker->uuid();
        $request->option = $request->size;
        $response = $this->basicRequest('POST', '/api/v0/items', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateVariantId()
    {
        $item = factory(Item::class, 'withRelations')->create();
        $request = factory(Item::class)->make();
        $request->sku = $request->manufacturer_sku;
        $request->variant_id = 0;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateSku()
    {
        $exist = factory(Item::class, 'withRelations')->create();
        $item = factory(Item::class, 'withRelations')->create();
        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $exist->manufacturer_sku;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdatePrices()
    {
        $item = factory(Item::class, 'withRelations')->create();
        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->wholesale_price = -1;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->retail_price = -1;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Item::class, 'withRelations')->make();
        $request->sku = $request->manufacturer_sku;
        $request->premium_price = -1;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateOption()
    {
        $exist = factory(Item::class, 'withRelations')->create();
        $item = factory(Item::class)->create([
            'product_id' => $exist->product_id,
            'variant_id' => $exist->variant_id,
        ]);
        $item->option = $exist->size;
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $item->toArray());
        $response->assertResponseStatus(422);
    }

    public function testDeleteWithInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item');
        $item = $inventory->item;
        $response = $this->basicRequest('DELETE', '/api/v0/items/'.$item->id);
        $response->assertResponseStatus(422);
    }
}
