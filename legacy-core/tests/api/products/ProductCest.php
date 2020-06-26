<?php
namespace category;

use \Step\Api\UserAuth;
use App\Models\Product;
use App\Models\Category;
use App\Models\Media;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Inventory;
use App\Models\ProductType;
use DB;

class ProductCest
{
    public function _before()
    {
        $this->product = Product::orderBy('name', 'ASC')->first();

        $this->productRequest = [
            'name' => 'Codeception Product',
            'slug' => 'codeception-product',
            'short_description' => 'This is a short description.',
            'long_description' => 'This is suppose to be a long description.',
            'type_id' => 1,
            'tax_class' => 00000000
        ];

        $this->requestAdditions = [
            'roles' => Role::all()->pluck('id')->toArray(),
            'tags' => ['codeception', 'product', 'test'],
            'categories' => [Category::first()->id],
            'images' => [Media::first()->id]
        ];

        $this->responseAdditions = [
            'roles' => Role::all()->toArray(),
            'category' => [Category::first()->toArray()],
        ];

        $this->item = [
            'size' => 'S',
            'print' => 'Blue',
            'weight' => 1.1,
            'length' => 1.2,
            'width' => 1.3,
            'height' => 1.4,
            'custom_sku' => 'codeception123',
            'is_default' => 1,
            'manufacturer_sku' => 'codeception123',
        ];

        $this->pricing = [
            'wholesale_price' => [
                'price' => 15
            ],
            'msrp' => [
                'price' => 20
            ],
            'premium_price' => [
                'price' => 30
            ],
        ];
    }

    public function _after()
    {
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->wantTo('Get all products');
        $I->loginAsAdmin();
        $request = [
            'category'   => 'all',
            'searchTerm' => '',
            'limit'      => 10,
            'column'     => 'name',
            'order'      => 'ASC'
        ];
        $I->sendAjaxRequest('GET', '/api/v1/products', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [[
                'name' => $this->product->name
            ]]
        ]);
    }

    public function tryGetWholesaleStore(UserAuth $I)
    {
        $inputs = [
            'searchTerm' => '',
            'sortBy' => 'name',
            'order' => 'ASC',
            'category' => null
        ];
        $I->wantTo('Get products for the WholesaleStore');
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/products/wholesale-store', $inputs);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'queryStrs' => [
                'searchTerm' => '',
                'sortBy' => 'name',
                'order' => 'ASC',
                'category' => null
            ],
            'sortOptions' => [
                'name' => 'Alphabetically, A-Z',
                'name_desc' => 'Alphabetically, Z-A',
                'price' => 'Price, low to high',
                'price_desc' => 'Price, high to low'
            ],
            'selectedValue' => 'name',
            'products' => [
                'data' => [
                    [
                        'id' => $this->product->id,
                        'name' =>  $this->product->name,
                        'slug' =>  $this->product->slug,
                    ]
                ]
            ]
        ]);
        // test data is appropriately formatted
        $I->seeResponseJsonMatchesJsonPath("$.queryStrs.searchTerm");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].id");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].name");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].slug");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].quantity_available");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].price");
        $I->seeResponseJsonMatchesJsonPath("$.products.data[*].media");
        // test data is appropriate types
        $I->seeResponseMatchesJsonType([
            'queryStrs' => [
                'searchTerm' => 'string',
                'sortBy' => 'string',
                'order' => 'string',
                'category' => 'string|null'
            ],
            'sortOptions' => [
                'name' => 'string',
                'name_desc' => 'string',
                'price' => 'string',
                'price_desc' => 'string'
            ],
            'selectedValue' => 'string',
            'products' => [
                'data' => [
                    [
                        'id' => 'integer',
                        'name' =>  'string',
                        'slug' =>  'string',
                        'media' => 'array',
                        'quantity_available' => 'string', // MySql cast this as a string.
                        'price' => 'string', // Mysql will automatically cast a decmial to be a string if the field is nullable (e.g. price)

                    ]
                ]
            ]
        ]);
    }

    public function tryPostCreateAdmin(UserAuth $I)
    {
        $I->wantTo('Create a new product');
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['user_id'] = 1;
        $request['items'][0] = $this->item;
        $response = $request;
        $request += $this->requestAdditions;
        $request['items'][0] += $this->pricing;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(200);
        $response += $this->responseAdditions;
        $I->seeResponseContainsJson($response);
        $I->seeRecord('products', $this->productRequest);
        $I->seeRecord('items', $this->item);
    }

    public function tryPostCreateRep(UserAuth $I)
    {
        $I->wantTo('Create a new product as rep');
        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Reps can create products", "show": true}',
        ]);
        $I->loginAsRep();
        $request = $this->productRequest;
        $request['user_id'] = 106;
        $request['items'][0] = $this->item;
        $response = $request;
        $request += $this->requestAdditions;
        $request['items'][0] += $this->pricing;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(200);
        $response += $this->responseAdditions;
        $I->seeResponseContainsJson($response);
        $I->seeRecord('products', $this->productRequest);
        $I->seeRecord('items', $this->item);
    }


    public function tryPostCreateNotResalable(UserAuth $I)
    {
        $I->wantTo('Create a new product that is not for resale');
        $I->loginAsAdmin();
        $this->productRequest['type_id'] = 6;
        $request = $this->productRequest;
        $request['user_id'] = 1;
        $request['items'][0] = $this->item;
        $response = $request;
        $request += $this->requestAdditions;
        $request['items'][0] += $this->pricing;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(200);
        $response += $this->responseAdditions;
        $I->seeResponseContainsJson($response);
        $I->seeRecord('products', $this->productRequest);
        $I->seeRecord('items', $this->item);
    }

    public function tryGetShow(UserAuth $I)
    {
        $I->wantTo('Show a specific product');
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/products/show/'.$this->product->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            $this->product->toArray()
        );
    }

    public function tryGetShowEdit(UserAuth $I)
    {
        $I->wantTo('Show a specific product to edit');
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/products/show-edit/'.$this->product->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->product->toArray());
    }

    public function tryPutEdit(UserAuth $I)
    {
        $I->wantTo('Edit a product.');
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $this->item;
        $response = $request;
        $request += $this->requestAdditions;
        $request['items'][0] += $this->pricing;
        $I->sendAjaxRequest('PUT', '/api/v1/products/edit', $request);
        $I->seeResponseCodeIs(200);
        $response += $this->responseAdditions;
        $I->seeResponseContainsJson($response);
        $I->seeRecord('products', $this->productRequest);
        $I->seeRecord('items', $this->item);
    }

    public function tryDelete(UserAuth $I)
    {
        $I->wantTo('Delete a product');
        $I->loginAsAdmin();
        $I->sendAjaxRequest('DELETE', '/api/v1/products/delete/'.$this->product->id);
        $I->seeResponseCodeIs(403);
        DB::beginTransaction();
        // Need to remove all inventory from the product to be able to delete it.
        foreach ($this->product->items as $item) {
            $inventories = Inventory::where('item_id', $item->id)->get();
            foreach ($inventories as $inventory) {
                $inventory->update(['quantity_available' => 0]);
            }
        }
        DB::commit();
        $I->sendAjaxRequest('DELETE', '/api/v1/products/delete/'.$this->product->id);
        $I->seeResponseCodeIs(200);
    }

    public function tryGetType(UserAuth $I)
    {
        $I->wantTo('Get all product Types');
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/products/type');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            0 => [
                'id' => 1,
                'name' => "Product",
            ]
        ]);
    }

    public function tryCreateFulfilledByCorporateProduct(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['user_id'] = config('site.apex_user_id');
        $request['type_id'] = 5;
        $request['items'][0] = $this->item;
        $response = $request;
        $request += $this->requestAdditions;
        $request['roles'] = [];
        $request['items'][0] += $this->pricing;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(200);
        $response += $this->responseAdditions;
        unset($response['roles']);
        $I->seeResponseContainsJson($response);
        $I->seeRecord('products', [
            'name' => $request['name'],
            'slug' => $request['slug'],
            'short_description' => $request['short_description'],
            'long_description' => $request['long_description'],
            'type_id' => 5
        ]);
        $I->seeRecord('items', $this->item);
    }
}
