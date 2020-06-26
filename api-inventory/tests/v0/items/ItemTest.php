<?php

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Product;
use App\Models\Media;
use App\Models\Variant;
use App\Models\Visibility;
use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\DbUnit\TestCaseTrait;

class ItemTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->itemStructure = [
            'id',
            'variant_id',
            'option',
            'sku',
            'location',
        ];
    }

    public function testFindBasic()
    {
        $item = factory(Item::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/items/'.$item->id);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure($this->itemStructure);
        $response->seeJson(array_only($item->toArray(), $this->itemStructure));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindAvailable()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item');
        $item = $inventory->item;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/items/'.$item->id, $request);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure($this->itemStructure);
        $response->seeJson(array_only($item->toArray(), $this->itemStructure));
    }

    public function testIndexBasic()
    {
        $item = factory(Item::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/items');
        $response->assertResponseStatus(200);
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    $this->itemStructure
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
        $image = factory(Media::class)->create();
        $product->images()->attach($image);
        $request = [
            'search_term' => $item->manufacturer_sku,
            'expands' => [
                'variant',
                'variant_images',
                'product',
                'product_images'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/items', $request);
        $response->assertResponseStatus(200);
        $productModel = new Product();
        $variantModel = new Variant();
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    $this->itemStructure
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
        ]);
        $response->seeJson($item->toArray());
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
        $response->seeJson([
            'id' => $product->id,
            'name' => $product->name
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
            $request = [
                'search_term' => $term,
                'expands' => [
                    'product',
                    'variant',
                ]
            ];
            $response = $this->basicRequest('GET', '/api/v0/items', $request);
            $response->assertResponseStatus(200);
            $response->seeJson([
                'id' => $product->id,
                'name' => $searchTerms['name']
            ]);
        }
    }

    public function testCreateBasic()
    {
        $item = factory(Item::class, 'withRelations')->make();
        $request = $item->toArray();
        $request['sku'] = $item->manufacturer_sku;
        $response = $this->basicRequest('POST', '/api/v0/items', $request);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure($this->itemStructure);
        $response->seeJson(array_only($item->toArray(), $this->itemStructure));
    }

    /**
     * @depends testCreateBasic
     */
    public function testCreateExtensive()
    {
        $faker = Factory::create();
        $item = factory(Item::class, 'withRelations')->make();
        $request = $item->toArray();
        $request['sku'] = $item->manufacturer_sku;
        $request['wholesale_price'] = money_format("%!n", $faker->numberBetween(2000, 3000) / 100);
        $request['retail_price'] = money_format("%!n", $faker->numberBetween(1000, 1999) / 100);
        $request['premium_price'] = money_format("%!n", $faker->numberBetween(1, 999) / 100);

        $response = $this->basicRequest('POST', '/api/v0/items', $request);
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $response->seeJsonStructure(array_merge($this->itemStructure, [
            'wholesale_price',
            'retail_price',
            'premium_price',
        ]));
        $this->seeInDatabase('items', $item->toArray());
        $content = json_decode($response->response->getContent());
        $this->seeInDatabase('prices', [
            'price_type_id' => 1,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['wholesale_price'],
        ]);
        $this->seeInDatabase('prices', [
            'price_type_id' => 2,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['retail_price'],
        ]);
        $this->seeInDatabase('prices', [
            'price_type_id' => 3,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['premium_price'],
        ]);
    }

    public function testUpdateBasic()
    {
        $item = factory(Item::class, 'withRelations')->create();
        $item->load('variant');
        $faker = Factory::create();
        $request = [
            'option' => 'Small',
            'location' => substr(str_shuffle("ABCDEFGHJKLMNOPQRSTUVWXYZ"), -7),
            'sku' => $faker->uuid(),
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request);
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $response->seeJsonStructure($this->itemStructure);
        $response->seeJson(array_only($request, $this->itemStructure));
        $response->seeInDatabase('items', [
            'id' => $item->id,
            'size' => $request['option'],
            'location' => $request['location'],
            'manufacturer_sku' => $request['sku'],
        ]);
    }

    /**
     * @depends testUpdateBasic
     */
    public function testUpdateExtensive()
    {
        $item = factory(Item::class, 'withRelations')->create();
        $item->load('variant');
        $faker = Factory::create();
        $request = [
            'option' => 'Small',
            'location' => substr(str_shuffle("ABCDEFGHJKLMNOPQRSTUVWXYZ"), -7),
            'sku' => $faker->uuid(),
            'wholesale_price' => money_format("%!n", ($faker->numberBetween(2000, 3000) / 100)),
            'retail_price' => money_format("%!n", ($faker->numberBetween(1000, 1999) / 100)),
            'premium_price' => money_format("%!n", ($faker->numberBetween(1, 999) / 100)),
        ];
        $response = $this->basicRequest('PATCH', '/api/v0/items/'.$item->id, $request);
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $response->seeJsonStructure($this->itemStructure);
        $response->seeJson(array_only($request, $this->itemStructure));
        $content = json_decode($response->response->getContent());
        $response->seeInDatabase('items', [
            'id' => $item->id,
            'size' => $request['option'],
            'location' => $request['location'],
            'manufacturer_sku' => $request['sku'],
        ]);
        $this->seeInDatabase('prices', [
            'price_type_id' => 1,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['wholesale_price'],
        ]);
        $this->seeInDatabase('prices', [
            'price_type_id' => 2,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['retail_price'],
        ]);
        $this->seeInDatabase('prices', [
            'price_type_id' => 3,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $content->id,
            'price' => $request['premium_price'],
        ]);
    }

    public function testDelete()
    {
        $item = factory(Item::class)->create();
        $response = $this->basicRequest('DELETE', '/api/v0/items/'.$item->id);
        $response->assertResponseStatus(200);
        $this->notSeeInDatabase('items', [
            'id' => $item->id,
            'deleted_at' => null,
        ]);
    }
}
