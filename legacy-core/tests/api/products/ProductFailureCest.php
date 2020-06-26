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

class ProductFailureCest
{
    public function _before()
    {
        $this->product = Product::first();

        $this->productRequest = [
            'name' => 'Codeception Product',
            'slug' => 'codeception-product',
            'short_description' => 'This is a short description.',
            'long_description' => 'This is suppose to be a long description.',
        ];

        $this->requestAdditions = [
            'roles' => Role::all()->pluck('id')->toArray(),
            'tags' => ['codeception', 'product', 'test'],
            'categories' => [Category::first()->id],
            'images' => [Media::first()]
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
            "is_default" => 1,
            "checked" => true,
            'manufacturer_sku' => 'codeception123',
            'premium_price' => [
                'price' => 30
            ],
            'wholesale_price' => [
                'price' => 15
            ],
            'msrp' => [
                'price' => 20
            ],
        ];
    }

    public function _after()
    {
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->wantTo('Get all products');
        $request = [
            'category' => 'all',
            'searchTerm' => ''
        ];
        $I->sendAjaxRequest('GET', '/api/v1/products', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryGetWholesaleStore(UserAuth $I)
    {
        $I->wantTo('Get products for the WholesaleStore');
        $I->sendAjaxRequest('GET', '/api/v1/products/wholesale-store', ['category' => 'all']);
        $I->seeResponseCodeIs(401);
    }

    public function tryPostCreate(UserAuth $I)
    {
        $I->wantTo('Create a new product');
        $request = $this->productRequest;
        $request['user_id'] = 106;
        $request['items']['0'] = $this->item;
        $request += $this->requestAdditions;
        $I->sendPOST('/api/v1/products/create', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryPostCreateRep(UserAuth $I)
    {
        $I->wantTo('Create a new product as rep with out permission');
        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Resellers can create products", "show": false}',
        ]);
        $I->loginAsRep();
        $request = $this->productRequest;
        $request['user_id'] = 106;
        $request['items'][0] = $this->item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(403);
    }

    public function tryCreateFulfilledByCorpWithRoles(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['type_id'] = 5;
        $request['items']['0'] = $this->item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
    }

    public function tryGetShowEdit(UserAuth $I)
    {
        $I->wantTo('Show a specific product to edit');
        $I->sendAjaxRequest('GET', '/api/v1/products/show-edit/'.$this->product->id);
        $I->seeResponseCodeIs(401);
    }

    public function tryPutEdit(UserAuth $I)
    {
        $I->wantTo('Edit a product.');
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $this->item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('PUT', '/api/v1/products/edit', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryDeleteDelete(UserAuth $I)
    {
        $I->wantTo('Delete a product');
        $I->sendAjaxRequest('DELETE', '/api/v1/products/delete/'.$this->product->id);
        $I->seeResponseCodeIs(401);

        $I->loginAsRep();
        $I->sendAjaxRequest('DELETE', '/api/v1/products/delete/'.$this->product->id);
        $I->seeResponseCodeIs(403);
    }

    public function tryToAddProductWithoutPrice(UserAuth $I)
    {
        $I->wantTo('Create a Product without a Price');
        $item = $this->item;
        unset($item['premium_price']);
        unset($item['wholesale_price']);
        unset($item['msrp']);
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('price');
    }

    public function tryToAddProductWithoutItem(UserAuth $I)
    {
        $I->wantTo('Create a Product without a Item');
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('items');
    }

    public function tryToAddProductWithoutIsDefault(UserAuth $I)
    {
        $I->wantTo('Create a Product without a default item');
        $item = $this->item;
        unset($item['is_default']);
        $I->loginAsAdmin();
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('is_default');
    }

    public function tryToAddProductWithoutName(UserAuth $I)
    {
        $I->wantTo('Create a Product without a Name');
        $I->loginAsAdmin();
        unset($this->productRequest['name']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $this->item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('name');
    }

    public function tryToAddProductWithoutSize(UserAuth $I)
    {
        $I->wantTo('Create a Product without a Size');
        $I->loginAsAdmin();
        $item = $this->item;
        unset($item['size']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('size');
    }

    public function tryToAddProductWithoutSku(UserAuth $I)
    {
        $I->wantTo('Create a Product without a Sku');
        $I->loginAsAdmin();
        $item = $this->item;
        unset($item['manufacturer_sku']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('manufacturer_sku');
    }

    public function tryToAddProductWithoutLength(UserAuth $I)
    {
        $I->wantTo('Create a Product without a length');
        $I->loginAsAdmin();
        $item = $this->item;
        unset($item['length']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('length');
    }

    public function tryToAddProductWithoutHeight(UserAuth $I)
    {
        $I->wantTo('Create a Product without a height');
        $I->loginAsAdmin();
        $item = $this->item;
        unset($item['height']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('height');
    }

    public function tryToAddProductWithoutWeight(UserAuth $I)
    {
        $I->wantTo('Create a Product without a weight');
        $I->loginAsAdmin();
        $item = $this->item;
        unset($item['weight']);
        $request = $this->productRequest;
        $request['id'] = $this->product->id;
        $request['items'][0] = $item;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/products/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('weight');
    }
}
