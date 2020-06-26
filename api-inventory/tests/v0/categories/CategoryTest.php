<?php

use App\Models\Category;
// use App\Models\Inventory;
// use App\Models\Item;
// use App\Models\Media;
// use App\Models\Price;
// use App\Models\Product;
// use App\Models\Variant;
// use App\Models\Visibility;
// use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class CategoryTest extends TestCase
{
    public function testFind()
    {
        $category = factory(Category::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/category/'.$category->id);
        $response->assertResponseStatus(200);
        $model = new Category();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($category->toArray(), $model->getFillable()));
    }

    public function testIndexBasic()
    {
        $category = factory(Category::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/category');
        $response->assertResponseStatus(200);
        $model = new Category();
        $response->seeJson(array_only($category->toArray(), $model->getFillable()));
    }

    public function testCreate()
    {
        $request = factory(Category::class)->make();
        $response = $this->basicRequest('POST', '/api/v0/category', $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Category();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($request->toArray(), $model->getFillable()));
    }

    public function testUpdate()
    {
        $request = factory(Category::class)->create();
        $response = $this->basicRequest('PATCH', '/api/v0/category/'.$request->id, $request->toArray());
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $model = new Category();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($request->toArray(), $model->getFillable()));
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/category/'.$category->id);
        $this->notSeeInDatabase('categories', [
            'id' => $category->id,
        ]);
    }
}
