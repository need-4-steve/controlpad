<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Media;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Visibility;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class ProductFailureTest extends TestCase
{
    public function testFindAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindAvailableZeroInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $inventory->load('item.variant.product');
        $product = $inventory->item->variant->product;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindPrice()
    {
        $request = ['price' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindOwnerId()
    {
        $request = ['owner_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindExpands()
    {
        $request = ['expands' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);

        $request = ['expands' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindVisibilities()
    {
        $request = ['visibilities' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);

        $product = factory(Product::class)->create();
        $request = ['visibilities' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindCategory()
    {
        $request = ['categories' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/0', $request);
        $response->assertResponseStatus(422);

        $product = factory(Product::class)->create();
        $request = ['categories' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testIndexAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/', $request);
        $response->assertResponseStatus(422);

        $request = ['available' => true];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);

        $inventory = factory(Inventory::class, 'withRelations')->create(['user_id' => 106]);
        $inventory->load('item.variant.product');
        $product = $inventory->item->variant->product;
        $request = [
            'available' => true,
            'user_id' => 1,
            'search_term' => $product->name,
        ];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJson(['data' => []]);
    }

    public function testIndexPrice()
    {
        $request = ['price' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexPriceInventory()
    {
        $request = ['price' => 'inventory'];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexOwnerId()
    {
        $request = ['owner_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products/', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexExpands()
    {
        $request = ['expands' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);

        $request = ['expands' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexVisibility()
    {
        $request = ['visibilities' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);

        $product = factory(Product::class)->create();
        $request = ['visibilities' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->seeJson(['data' => []]);
    }

    public function testIndexCategory()
    {
        $request = ['categories' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(422);

        $product = factory(Product::class)->create();
        $request = ['categories' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->seeJson(['data' => []]);
    }

    public function testCreateCategories()
    {
        $request = factory(Product::class)->make(['categories' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['categories' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateImages()
    {
        $request = factory(Product::class)->make(['images' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $product = factory(Product::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/products', $product->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateVisibilities()
    {
        $request = factory(Product::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateMinMax()
    {
        $request = factory(Product::class)->make(['min' => -1]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['min' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
        
        $request = factory(Product::class)->make(['min' => pi()]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['max' => -1]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['max' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
        
        $request = factory(Product::class)->make(['max' => pi()]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateNameAndSlug()
    {
        $request = factory(Product::class)->create();
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateUserId()
    {
        $request = factory(Product::class)->make(['user_id' => null]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateTypeId()
    {
        $request = factory(Product::class)->make(['type_id' => 0]);
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateCategories()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->make(['categories' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['categories' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateImages()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->make(['images' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateVisibilities()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateMinMax()
    {
        $product = factory(Product::class)->create();

        $request = factory(Product::class)->make(['min' => -1]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['min' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
        
        $request = factory(Product::class)->make(['min' => pi()]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['max' => -1]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Product::class)->make(['max' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
        
        $request = factory(Product::class)->make(['max' => pi()]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateNameAndSlug()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->create();
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateUserId()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->make(['user_id' => null]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateTypeId()
    {
        $product = factory(Product::class)->create();
        $request = factory(Product::class)->make(['type_id' => 0]);
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testDeleteDouble()
    {
        $product = factory(Product::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/products/'.$product->id);
        $response->assertResponseStatus(200);
        $response = $this->basicRequest('DELETE', '/api/v0/products/'.$product->id);
        $response->assertResponseStatus(422);
    }

    public function testDeleteWithInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item.variant.product');
        $product = $inventory->item->variant->product;
        $response = $this->basicRequest('DELETE', '/api/v0/products/'.$product->id);
        $response->assertResponseStatus(422);
    }
}
