<?php

use App\Models\Category;
use App\Models\Inventory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class CategoryFailureTest extends TestCase
{
    public function testFind()
    {
        $response = $this->basicRequest('GET', '/api/v0/category/0');
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(404);
    }

    public function testCreate()
    {
        $uniqid = uniqid();
        $category = factory(Category::class)->create(['name' => 'error'.$uniqid]);
        $request = factory(Category::class)->make(['name' => 'error'.$uniqid]);
        $response = $this->basicRequest('POST', '/api/v0/category', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Category::class)->make(['placement' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/category', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Category::class)->make(['parent_id' => 'error']);
        $response = $this->basicRequest('POST', '/api/v0/category', $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Category::class)->make(['parent_id' => '']);
        $response = $this->basicRequest('POST', '/api/v0/category', $request->toArray());
        $response->assertResponseStatus(422);
    }

    public function testUpdate()
    {
        $uniqid = uniqid();
        $category = factory(Category::class)->create(['name' => 'error'.$uniqid]);
        $request = factory(Category::class)->make(['placement' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/category/'.$category->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Category::class)->make(['parent_id' => 'error']);
        $response = $this->basicRequest('PATCH', '/api/v0/category/'.$category->id, $request->toArray());
        $response->assertResponseStatus(422);

        $request = factory(Category::class)->make(['parent_id' => '']);
        $response = $this->basicRequest('PATCH', '/api/v0/category/'.$category->id, $request->toArray());
        $response->assertResponseStatus(422);
    }
}
