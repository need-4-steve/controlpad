<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Media;
use App\Models\Price;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Visibility;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class ProductTest extends TestCase
{
    private function getSeeableFields($array)
    {
        $fields = [
            'long_description',
            'max',
            'min',
            'name',
            'short_description',
            'slug',
            'type_id',
            'user_id',
            'tax_class',
        ];
        return array_only($array, $fields);
    }

    public function testFindBasic()
    {
        $product = factory(Product::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id);
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($this->getSeeableFields($product->toArray()));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindExtensive()
    {
        $product = factory(Product::class)->create();
        $request = [
            'expands' => [
                'categories',
                'product_images',
                'variants',
                'visibilities'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'variants',
            'visibilities'
        ]));
        $response->seeJson($this->getSeeableFields($product->toArray()));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindAvailable()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item.variant.product');
        $product = $inventory->item->variant->product;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($this->getSeeableFields($product->toArray()));
    }

    public function testIndexBasic()
    {
        $product = factory(Product::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/products');
        $response->assertResponseStatus(200);
        $model = new Product();
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
        $product = factory(Product::class)->create();
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id
        ]);
        $item = factory(Item::class)->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'print' => $variant->name
        ]);
        $category = factory(Category::class)->create();
        $product->categories()->attach($category->id);
        $image = factory(Media::class)->create();
        $product->images()->attach($image);
        $visibilities = Visibility::all();
        $product->visibilities()->sync($visibilities);
        $variant->visibilities()->sync($visibilities);
        $request = [
            'categories' => [$category->id],
            'visibilities' => $visibilities,
            'expands' => [
                'categories',
                'product_images',
                'variants',
                'visibilities'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(200);
        $productModel = new Product();
        $variantModel = new Variant();
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    array_merge($productModel->getFillable(), [
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
                        'variants' => [
                            $variantModel->getFillable()
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
        $response->seeJson($this->getSeeableFields($product->toArray()));
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
        $response->seeJson([
            'id' => $variant->id,
            'name' => $variant->name
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
            'sku' => $faker->uuid(),
            'option' => $faker->colorName().'-'.uniqid(),
            'variant' => $faker->colorName().'-'.uniqid(),
            'short_description' => $faker->uuid(),
            'long_description' => $faker->uuid(),
        ];
        $product = factory(Product::class)->create([
            'name' => $searchTerms['name'],
            'short_description' => $searchTerms['short_description'],
            'long_description' => $searchTerms['long_description'],
        ]);
        $variant = factory(Variant::class)->create([
            'product_id' => $product->id,
            'name' => $searchTerms['variant']
        ]);
        $item = factory(Item::class)->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'print' => $variant->name,
            'manufacturer_sku' => $searchTerms['sku'],
            'size' => $searchTerms['option'],
            'print' => $searchTerms['variant'],
        ]);
        foreach ($searchTerms as $term) {
            $request['search_term'] = $term;
            $response = $this->basicRequest('GET', '/api/v0/products', $request);
            $response->assertResponseStatus(200);
            $response->seeJson([
                'id' => $product->id,
                'name' => $searchTerms['name']
            ]);
            $response->seeJson($this->getSeeableFields($product->toArray()));
        }
    }

    /**
     * @depends testIndexSearch
     */
    public function testIndexInventory()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create([
            'user_id' => 1,
            'owner_id' => 1
        ]);
        $item = Item::find($inventory->item_id);
        $variant = $item->variant;
        $product = $variant->product;
        $request = [
            'user_id' => 1,
            'available' => true,
            'search_term' => $product->name,
            'expands' => [
                'variants',
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/products', $request);
        $response->assertResponseStatus(200);
        $response->seeJson($this->getSeeableFields($product->toArray()));
        $response->seeJson([
            'id' => $product->id,
            'name' => $product->name
        ]);
    }

    /**
     * @depends testIndexInventory
     */
    public function testIndexPrices()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create([
            'user_id' => 1,
            'owner_id' => 1
        ]);
        $item = Item::find($inventory->item_id);
        $variant = $item->variant;
        $product = $variant->product;
        $premium = factory(Price::class, 'premium')->create(['priceable_id' => $item->id]);
        $item->premium_price = $premium->price;
        $retail = factory(Price::class, 'retail')->create(['priceable_id' => $item->id]);
        $item->retail_price = $retail->price;
        $wholesale = factory(Price::class, 'wholesale')->create(['priceable_id' => $item->id]);
        $item->wholesale_price = $wholesale->price;
        $inventoryPrice = factory(Price::class, 'inventory')->create(['priceable_id' => $inventory->id]);
        $inventory->inventory_price = $inventoryPrice->price;
        $inventory->save();
        $item->save();
        $prices = [
            'premium' => $premium->price,
            'retail' => $retail->price,
            'wholesale' => $wholesale->price,
            'inventory' => $inventoryPrice->price,
        ];
        foreach ($prices as $key => $price) {
            $request = [
                'user_id' => 1,
                'available' => true,
                'search_term' => $product->name,
                'sort_by' => 'price',
                'price' => $key,
                'expands' => [
                    'variants',
                ]
            ];
            $response = $this->basicRequest('GET', '/api/v0/products', $request);
            $response->assertResponseStatus(200);
            $response->seeJson($this->getSeeableFields($product->toArray()));
            $response->seeJson([
                'id' => $product->id,
                'name' => $product->name,
                'price' => money_format("%!n", $price),
            ]);
            $response->seeJson([
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => money_format("%!n", $price),
            ]);
            $response->seeJson([
                'id' => $item->id,
                'option' => $item->size,
                $key.'_price' => money_format("%!n", $price),
            ]);
        }
    }

    public function testCreateBasic()
    {
        $request = factory(Product::class)->make();
        $response = $this->basicRequest('POST', '/api/v0/products', $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($this->getSeeableFields($request->toArray()));
    }

    /**
     * @depends testCreateBasic
     */
    public function testCreateExtensive()
    {
        $product = factory(Product::class)->make();
        $cateogry = factory(Category::class)->create();
        $request['categories'][] = ['id' => $cateogry->id];
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $product->toArray());
        $response = $this->basicRequest('POST', '/api/v0/products', $request);
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'visibilities',
        ]));
        $response->seeJson($this->getSeeableFields($product->toArray()));
        $this->seeInDatabase('products', $product->toArray());
        $productResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('product_category', [
            'product_id' => $productResponse->id,
            'category_id' => $cateogry->id,
        ]);
        $this->seeInDatabase('mediables', [
            'media_id' => $image->id,
            'mediable_id' => $productResponse->id,
            'mediable_type' => Product::class
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('product_visibility', [
                'product_id' => $productResponse->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testUpdateBasic()
    {
        $request = factory(Product::class)->create();
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$request->id, $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson($this->getSeeableFields($request->toArray()));
    }

    /**
     * @depends testUpdateBasic
     */
    public function testUpdateExtensive()
    {
        $product = factory(Product::class)->create();
        $productUpdate = factory(Product::class)->make();
        $cateogry = factory(Category::class)->create();
        $request['categories'][] = ['id' => $cateogry->id];
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $productUpdate->toArray());
        $response = $this->basicRequest('PATCH', '/api/v0/products/'.$product->id, $request);
        $response->assertResponseStatus(200);
        $model = new Product();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'categories',
            'images',
            'visibilities',
        ]));
        $response->seeJson($this->getSeeableFields($request));
        $this->seeInDatabase('products', $productUpdate->toArray());
        $productResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('product_category', [
            'product_id' => $product->id,
            'category_id' => $cateogry->id,
        ]);
        $this->seeInDatabase('mediables', [
            'media_id' => $image->id,
            'mediable_id' => $product->id,
            'mediable_type' => Product::class
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('product_visibility', [
                'product_id' => $product->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testDelete()
    {
        $product = factory(Product::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/products/'.$product->id);
        $this->notSeeInDatabase('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }
}
