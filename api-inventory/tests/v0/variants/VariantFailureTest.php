<?php

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Media;
use App\Models\Variant;
use App\Models\Visibility;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class VariantFailureTest extends TestCase
{
    public function testFindAvailable()
    {
        $request = ['available' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(422);
    }

    public function testFindAvailableZeroInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $inventory->load('item.variant');
        $variant = $inventory->item->variant;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/variants/'.$variant->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testFindPrice()
    {
        $request = ['price' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindUserId()
    {
        $request = ['user_id' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindExpands()
    {
        $request = ['expands' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $response->assertResponseStatus(422);

        $request = ['expands' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $response->assertResponseStatus(422);
    }

    public function testFindVisibilities()
    {
        $request = ['visibilities' => 'error'];
        $response = $this->basicRequest('GET', '/api/v0/variants/0', $request);
        $response->assertResponseStatus(422);

        $variant = factory(Variant::class)->create();
        $request = ['visibilities' => ['error']];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$variant->id, $request);
        $response->assertResponseStatus(404);
    }

    public function testCreateImages()
    {
        $request = factory(Variant::class)->make(['images' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $variant = factory(Variant::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $variant->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateVisibilities()
    {
        $request = factory(Variant::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testCreateMinMax()
    {
        $request = factory(Variant::class)->make(['min' => -1]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['min' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['min' => pi()]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => -1]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => pi()]);
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateImages()
    {
        $variant = factory(Variant::class)->create();
        $request = factory(Variant::class)->make(['images' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['images' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateVisibilities()
    {
        $variant = factory(Variant::class)->create();
        $request = factory(Variant::class)->make(['visibilities' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['visibilities' => [
            [
                'id' => 'error'
            ]
        ]]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdateMinMax()
    {
        $variant = factory(Variant::class)->create();
        $request = factory(Variant::class)->make(['min' => -1]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['min' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['min' => pi()]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => -1]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Variant::class)->make(['max' => pi()]);
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testDeleteDouble()
    {
        $variant = factory(Variant::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/variants/'.$variant->id);
        $response->assertResponseStatus(200);
        $response = $this->basicRequest('DELETE', '/api/v0/variants/'.$variant->id);
        $response->assertResponseStatus(422);
    }

    public function testDeleteWithInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item.variant');
        $variant = $inventory->item->variant;
        $response = $this->basicRequest('DELETE', '/api/v0/variants/'.$variant->id);
        $response->assertResponseStatus(422);
    }
}
