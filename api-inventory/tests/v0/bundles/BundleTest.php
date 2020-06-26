<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Media;
use App\Models\Price;
use App\Models\Bundle;
use App\Models\Variant;
use App\Models\Visibility;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class BundleTest extends TestCase
{
    public function testFindBasic()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id);
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure($model->getFillable());
    }

    /**
     * @depends testFindBasic
     */
    public function testFindExtensive()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 0]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $request = [
            'expands' => [
                'categories',
                'bundle_images',
                'items',
                'products',
                'product_images',
                'variants',
                'variant_images',
                'visibilities'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'visibilities',
            'items' => [
                '*' => [
                    'variant' => [
                        'images',
                        'product' => [
                            'images'
                        ]
                    ]
                ]
            ]
        ]));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindAvailable()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1, 'user_id' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($bundle->toArray());
    }

    public function testIndexBasic()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $response = $this->basicRequest('GET', '/api/v0/bundles');
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    $model->getFillable()
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
        ]);
    }

    /**
     * @depends testIndexBasic
     */
    public function testIndexExpands()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $category = factory(Category::class)->create();
        $bundle->categories()->attach($category->id);
        $image = factory(Media::class)->create();
        $bundle->images()->attach($image);
        $visibilities = Visibility::all();
        $bundle->visibilities()->sync($visibilities);
        $request = [
            'categories' => ['id' => $category->id],
            'visibilities' => $visibilities,
            'expands' => [
                'categories',
                'bundle_images',
                'items',
                'products',
                'product_images',
                'variants',
                'variant_images',
                'visibilities'
            ],
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(200);
        $bundleModel = new Bundle();
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    array_merge($bundleModel->getFillable(), [
                        'categories' => [
                            [
                                'id',
                                'name'
                            ]
                        ],
                        'images' => [
                            [
                                'id',
                                'url'
                            ]
                        ],
                        'visibilities' => [
                            [
                                'id',
                                'name'
                            ]
                        ]
                    ])
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
        ]);
        $response->seeJson([
            'categories' => [
                [
                    'id' => $category->id,
                    'name' => $category->name
                ]
            ],
        ]);
        $response->seeJson([
            'visibilities' => $visibilities
        ]);
        $response->seeJson([
            'images' => [
                [
                    'id' => $image->id,
                    'url' => $image->url
                ]
            ]
        ]);
    }

    /**
     * @depends testIndexBasic
     */
    public function testIndexSearch()
    {
        $faker = Factory::create();
        $searchTerms = [
            'name' => $faker->name().' '.uniqid(),
            'short_description' => $faker->sentence(),
            'long_description' => $faker->paragraph(2),
        ];
        $bundle = factory(Bundle::class)->create($searchTerms);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        foreach ($searchTerms as $term) {
            $request['search_term'] = $term;
            $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
            $response->assertResponseStatus(200);
            $response->seeJson([
                'id' => $bundle->id,
                'name' => $searchTerms['name']
            ]);
        }
    }

    /**
     * @depends testIndexSearch
     */
    public function testIndexInventory()
    {
        $bundle = factory(Bundle::class)->create();
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1, 'user_id' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $request = [
            'user_id' => 1,
            'available' => true,
            'search_term' => $bundle->name,
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'id' => $bundle->id,
            'name' => $bundle->name
        ]);
    }

    /**
     * @depends testIndexInventory
     */
    public function testIndexPrices()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1, 'user_id' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $price = factory(Price::class, 'bundle')->create(['priceable_id' => $bundle->id]);

        $request = [
            'user_id' => 1,
            'available' => true,
            'search_term' => $bundle->name,
            'sort_by' => 'wholesale_price',
        ];
        $response = $this->basicRequest('GET', '/api/v0/bundles', $request);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'id' => $bundle->id,
            'name' => $bundle->name
        ]);
    }

    public function testCreateBasic()
    {
        $request = factory(Bundle::class)->make();
        $request['wholesale_price'] = factory(Price::class, 'bundle')->make()->price;
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($request->toArray());
    }

    /**
     * @depends testCreateBasic
     */
    public function testCreateExtensive()
    {
        $bundle = factory(Bundle::class)->make();
        $request['wholesale_price'] = factory(Price::class, 'bundle')->make()->price;
        $cateogry = factory(Category::class)->create();
        $request['categories'][] = ['id' => $cateogry->id];
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $bundle->toArray());
        $response = $this->basicRequest('POST', '/api/v0/bundles', $request);
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'visibilities',
        ]));
        $response->seeJson($bundle->toArray());
        $this->seeInDatabase('bundles', $bundle->toArray());
        $bundleResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('bundle_category', [
            'bundle_id' => $bundleResponse->id,
            'category_id' => $cateogry->id,
        ]);
        $this->seeInDatabase('mediables', [
            'media_id' => $image->id,
            'mediable_id' => $bundleResponse->id,
            'mediable_type' => Bundle::class
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('bundle_visibility', [
                'bundle_id' => $bundleResponse->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testUpdateBasic()
    {
        $request = factory(Bundle::class)->create();
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$request->id, $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($request->toArray());
    }

    /**
     * @depends testUpdateBasic
     */
    public function testUpdateExtensive()
    {
        $bundle = factory(Bundle::class)->create();
        $bundleUpdate = factory(Bundle::class)->make();
        $cateogry = factory(Category::class)->create();
        $request['categories'][] = ['id' => $cateogry->id];
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $bundleUpdate->toArray());
        $response = $this->basicRequest('PATCH', '/api/v0/bundles/'.$bundle->id, $request);
        $response->assertResponseStatus(200);
        $model = new Bundle();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'visibilities',
        ]));
        $response->seeJson($bundleUpdate->toArray());
        $this->seeInDatabase('bundles', $bundleUpdate->toArray());
        $bundleResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('bundle_category', [
            'bundle_id' => $bundle->id,
            'category_id' => $cateogry->id,
        ]);
        $this->seeInDatabase('mediables', [
            'media_id' => $image->id,
            'mediable_id' => $bundle->id,
            'mediable_type' => Bundle::class
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('bundle_visibility', [
                'bundle_id' => $bundle->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testDelete()
    {
        $bundle = factory(Bundle::class)->create(['user_id' => 1]);
        $inventory = factory(Inventory::class, 'withRelations')->create(['quantity_available' => 1, 'user_id' => 1]);
        $bundle->items()->attach($inventory['item_id'], ['quantity' => 1]);
        $cateogry = factory(Category::class)->create();
        $bundle->categories()->attach($cateogry->id);
        $image = factory(Media::class)->create();
        $bundle->images()->attach($image->id);
        $bundle->visibilities()->sync([3, 5, 7, 8]);
        $response = $this->basicRequest('DELETE', '/api/v0/bundles/'.$bundle->id);
        $this->seeInDatabase('bundles', [
            'id' => $bundle->id,
        ]);
        $this->notSeeInDatabase('bundles', [
            'id' => $bundle->id,
            'deleted_at' => null,
        ]);
        $this->notSeeInDatabase('bundle_category', [
            'bundle_id' => $bundle->id,
        ]);
        $this->notSeeInDatabase('bundle_item', [
            'bundle_id' => $bundle->id,
        ]);
        $this->notSeeInDatabase('mediables', [
            'mediable_id' => $bundle->id,
            'mediable_type' => Bundle::class
        ]);
        $this->notSeeInDatabase('bundle_visibility', [
            'bundle_id' => $bundle->id,
        ]);
    }
}
