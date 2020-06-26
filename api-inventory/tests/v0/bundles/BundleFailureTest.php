<?php

use App\Models\Bundle;
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

class BundleFailureTest extends TestCase
{
    public function testFindAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindAvailableZeroInventory()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindExpands()
    {
        $request = ['expands' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);

        $request = ['expands' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindVisibilities()
    {
        $request = ['visibilities' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);

        $bundle = factory(Bundle::class)->create();
        $request = ['visibilities' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindCategory()
    {
        $request = ['categories' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/0', $request);
        $response->assertResponseStatus(422);

        $bundle = factory(Bundle::class)->create();
        $request = ['categories' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testIndexAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/', $request);
        $response->assertResponseStatus(422);

        $request = ['available' => true];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(422);

        $bundle = factory(Bundle::class)->create(['user_id' => 106]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $request = [
            'available' => true,
            'user_id' => 1,
            'search_term' => $bundle->name
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(200);
        $response->seeJson(['data' => []]);
    }

    public function testIndexUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles/', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexExpands()
    {
        $request = ['expands' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(422);

        $request = ['expands' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(422);
    }

    public function testIndexVisibility()
    {
        $request = ['visibilities' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(422);

        $bundles = factory(Bundle::class)->create();
        $request = ['visibilities' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->seeJson(['data' => []]);
    }

    public function testIndexCategory()
    {
        $request = ['categories' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(422);

        $bundles = factory(Bundle::class)->create();
        $request = ['categories' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->seeJson(['data' => []]);
    }

    public function testCreateCategories()
    {
        $request = factory(Bundle::class)->make(['categories' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Bundle::class)->make(['categories' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateImages()
    {
        $request = factory(Bundle::class)->make(['images' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);

        $bundles = factory(Bundle::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $bundles->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateVisibilities()
    {
        $request = factory(Bundle::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Bundle::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateNameAndSlug()
    {
        $request = factory(Bundle::class)->create();
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateUserId()
    {
        $request = factory(Bundle::class)->make(['user_id' => null]);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateTypeId()
    {
        $request = factory(Bundle::class)->make(['type_id' => 0]);
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateCategories()
    {
        $bundle = factory(Bundle::class)->create();
        $request = factory(Bundle::class)->make(['categories' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundle->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Bundle::class)->make(['categories' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundle->id, $request->toArray());
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(422);
    }

    public function testUpdateImages()
    {
        $bundles = factory(Bundle::class)->create();
        $request = factory(Bundle::class)->make(['images' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Bundle::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateVisibilities()
    {
        $bundles = factory(Bundle::class)->create();
        $request = factory(Bundle::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Bundle::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateNameAndSlug()
    {
        $bundles = factory(Bundle::class)->create();
        $request = factory(Bundle::class)->create();
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateUserId()
    {
        $bundles = factory(Bundle::class)->create();
        $request = factory(Bundle::class)->make(['user_id' => null]);
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundles->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testDeleteDouble()
    {
        $bundles = factory(Bundle::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/bundles/'.$bundles->id);
        $response->assertResponseStatus(200);
        $response = $this->basicRequest('DELETE', '/api/v0/bundles/'.$bundles->id);
        $response->assertResponseStatus(422);
    }
}
