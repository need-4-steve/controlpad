<?php

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

class VariantTest extends TestCase
{
    public function testFindBasic()
    {
        $variant = factory(Variant::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/variants/'.$variant->id);
        $model = new Variant();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($variant->toArray(), $model->getFillable()));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindExtensive()
    {
        $variant = factory(Variant::class)->create();
        $request = [
            'expands' => [
                'variant_images',
                'visibilities'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/variants/'.$variant->id, $request);
        $object = json_decode($response->response->getContent());
        $model = new Variant();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'images',
            'items',
            'visibilities'
        ]));
        $response->seeJson(array_only($variant->toArray(), $model->getFillable()));
    }

    /**
     * @depends testFindBasic
     */
    public function testFindAvailable()
    {
        $inventory = factory(Inventory::class, 'withRelations')->create();
        $inventory->load('item.variant');
        $variant = $inventory->item->variant;
        $request = [
            'user_id' => $inventory->user_id,
            'available' => true,
        ];
        $response = $this->basicRequest('GET', '/api/v0/variants/'.$variant->id, $request);
        $response->assertResponseStatus(200);
        $model = new Variant();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($variant->toArray(), $model->getFillable()));
    }


    public function testIndexBasic()
    {
        $variant = factory(Variant::class)->create();
        $request = [];
        $response = $this->basicRequest('GET', '/api/v0/variants', $request);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    [
                        'id',
                        'product_id',
                        'name',
                        'option_label',
                        'min',
                        'max',
                        'created_at',
                        'updated_at'
                    ]
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
        $variant->images()->attach($image);
        $request = [
            'search_term' => $item->manufacturer_sku,
            'expands' => [
                'visibilities',
                'variant_images',
                'product',
                'product_images'
            ]
        ];
        $response = $this->basicRequest('GET', '/api/v0/variants', $request);
        $response->assertResponseStatus(200);
        $productModel = new Product();
        $variantModel = new Variant();
        $response->seeJsonStructure([
                'current_page',
                'data' => [
                    [
                        'id',
                        'product_id',
                        'name',
                        'option_label',
                        'min',
                        'max',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
        ]);
        $response->seeJson($variant->toArray());
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
                ]
            ];
            $response = $this->basicRequest('GET', '/api/v0/variants', $request);
            $response->assertResponseStatus(200);
            $response->seeJson([
                'id' => $variant->id,
                'name' => $searchTerms['variant']
            ]);
        }
    }

    public function testCreateBasic()
    {
        $request = factory(Variant::class, 'withRelations')->make();
        $response = $this->basicRequest('POST', '/api/v0/variants', $request->toArray());
        $response->assertResponseStatus(200);
        $model = new Variant();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($request->toArray(), $model->getFillable()));
    }

    /**
     * @depends testCreateBasic
     */
    public function testCreateExtensive()
    {
        $variant = factory(Variant::class, 'withRelations')->make();
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $variant->toArray());
        $response = $this->basicRequest('POST', '/api/v0/variants', $request);
        $response->assertResponseStatus(200);
        $object = json_decode($response->response->getContent());
        $model = new Variant();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'images',
            'visibilities'
        ]));
        $response->seeJson(array_only($request, $model->getFillable()));
        $this->seeInDatabase('variants', $variant->toArray());
        $variantResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('media_variant', [
            'media_id' => $image->id,
            'variant_id' => $variantResponse->id,
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('variant_visibility', [
                'variant_id' => $variantResponse->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testUpdateBasic()
    {
        $request = factory(Variant::class, 'withRelations')->create();
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$request->id, $request->toArray());
        $object = json_decode($response->response->getContent());
        $response->assertResponseStatus(200);
        $model = new Variant();
        $response->seeJsonStructure($model->getFillable());
        $response->seeJson(array_only($request->toArray(), $model->getFillable()));
    }

    /**
     * @depends testUpdateBasic
     */
    public function testUpdateExtensive()
    {
        $variant = factory(Variant::class, 'withRelations')->create();
        $variantUpdate = factory(Variant::class)->make(['product_id' => $variant->product_id]);
        $image = factory(Media::class)->create();
        $request['images'][] = ['id' => $image->id];
        $visibilities = Visibility::select('id')->get();
        $request['visibilities'] = $visibilities->toArray();
        $request = array_merge($request, $variantUpdate->toArray());
        $response = $this->basicRequest('PATCH', '/api/v0/variants/'.$variant->id, $request);
        $response->assertResponseStatus(200);
        $model = new Variant();
        $response->seeJsonStructure(array_merge($model->getFillable(), [
            'images',
            'visibilities',
        ]));
        $response->seeJson(array_only($request, $model->getFillable()));
        $this->seeInDatabase('variants', $variantUpdate->toArray());
        $variantResponse = json_decode($response->response->getContent());
        $this->seeInDatabase('media_variant', [
            'media_id' => $image->id,
            'variant_id' => $variantResponse->id,
        ]);
        foreach ($visibilities as $visibility) {
            $this->seeInDatabase('variant_visibility', [
                'variant_id' => $variantResponse->id,
                'visibility_id' => $visibility->id,
            ]);
        }
    }

    public function testDelete()
    {
        $variant = factory(Variant::class, 'withRelations')->create();
        $response = $this->basicRequest('DELETE', '/api/v0/variants/'.$variant->id);
        $response->assertResponseStatus(200);
        $this->notSeeInDatabase('variants', [
            'id' => $variant->id,
            'deleted_at' => null,
        ]);
    }
}
