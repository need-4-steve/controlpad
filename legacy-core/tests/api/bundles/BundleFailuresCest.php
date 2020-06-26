<?php
namespace bundles;

use \Step\Api\UserAuth;
use App\Models\Bundle;
use App\Models\Item;
use App\Models\Role;
use App\Models\Category;
use App\Models\Media;
use App\Models\Tag;
use DB;

class BundleFailuresCest
{
    public function _before(UserAuth $I)
    {
        $this->bundle = Bundle::where('user_id', config('site.apex_user_id'))->first();
        $this->bundleEdit = Bundle::where('user_id', 106)->first();

        $this->items = Item::whereHas('product', function ($query) {
            $query->where('type_id', 1);
        })->get()->random(5);
        foreach ($this->items as $item) {
            $item['quantity'] = rand(1, 9);
        }

        $this->bundleRequest = [
            'name' => 'Codeception Product',
            'slug' => 'codeception-product',
            'short_description' => 'This is a short description.',
            'long_description' => 'This is suppose to be a long description.',
            'starter_kit' => false
        ];

        $this->requestAdditions = [
            'roles' => Role::all()->pluck('id')->toArray(),
            'tags' => ['codeception', 'product', 'test'],
            'categories' => [Category::first()->id],
            'images' => [Media::first()->id]
        ];

        $this->price = 9090.99;

        $this->customer = Role::whereName('Customer')->first()->id;
        $this->rep = Role::whereName('Rep')->first()->id;
        $this->admin = Role::whereName('Admin')->first()->id;
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->wantTo('Get all bundles');
        $I->sendAjaxRequest('GET', '/api/v1/bundles', ['searchTerm' => ""]);
        $I->seeResponseCodeIs(401);
    }

    public function tryPostCreate(UserAuth $I)
    {
        $I->wantTo('Create a bundle');
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(401);
    }
    public function tryPostCreateRep(UserAuth $I)
    {
        $I->wantTo('Create a bundle as a rep without auth');
        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Reps can create products", "show": false}',
        ]);
        $I->loginAsRep();
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(403);
    }

    public function tryGetBundlesByRoleCustomer(UserAuth $I)
    {
        $I->wantTo('Get bundles by role Customer');
        $this->bundle->roles()->sync([$this->rep, $this->admin]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'bundles' => $this->bundle->toArray()
        ]);
    }

    public function tryGetBundlesByRoleRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Get bundles by role Rep');
        $this->bundle->roles()->sync([$this->customer, $this->admin]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'bundles' => $this->bundle->toArray()
        ]);
    }

    public function tryGetBundlesByRoleAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Get bundles by role Admin');
        $this->bundle->roles()->sync([$this->customer, $this->rep]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'bundles' => $this->bundle->toArray()
        ]);
    }

    public function tryPutEdit(UserAuth $I)
    {
        $I->wantTo('Edit a bundle');
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $request['type_id'] = 1;

        $I->sendAjaxRequest('PUT', '/api/v1/bundles/edit/'.$this->bundleEdit->id, $request);
        $I->seeResponseCodeIs(401);

        $I->loginAsRep();
        $I->sendAjaxRequest('PUT', '/api/v1/bundles/edit/'.$this->bundleEdit->id, $request);
        $I->seeResponseCodeIs(200);
    }

    public function tryDeleteDelete(UserAuth $I)
    {
        $I->wantTo('Delete a bundle');
        $I->sendAjaxRequest('DELETE', '/api/v1/bundles/delete/'.$this->bundle->id);
        $I->seeResponseCodeIs(401);

        $I->loginAsRep();
        $I->sendAjaxRequest('DELETE', '/api/v1/bundles/delete/'.$this->bundle->id);
        $I->seeResponseCodeIs(401);
    }

    public function tryPostCreateWithoutName(UserAuth $I)
    {
        $I->wantTo('Create a bundle without a name');
        $I->loginAsAdmin();
        $bundleRequest = [
            'slug' => 'codeception-product',
            'short_description' => 'This is a short description.',
            'long_description' => 'This is suppose to be a long description.'
        ];
        $request = $bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('name');
    }

    public function tryPostCreateWithoutSlug(UserAuth $I)
    {
        $I->wantTo('Create a bundle without a slug');
        $I->loginAsAdmin();
        $bundleRequest = [
            'name' => 'Codeception Product',
            'short_description' => 'This is a short description.',
            'long_description' => 'This is suppose to be a long description.'
        ];
        $request = $bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('slug');
    }
    public function tryPostCreateWithoutShort(UserAuth $I)
    {
        $I->wantTo('Create a bundle without a short_description');
        $I->loginAsAdmin();
        $bundleRequest = [
            'name' => 'Codeception Product',
            'slug' => 'codeception-product',
            'long_description' => 'This is suppose to be a long description.'
        ];
        $request = $bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('short description');
    }
    public function tryPostCreateWithoutLong(UserAuth $I)
    {
        $I->wantTo('Create a bundle without a long_description');
        $I->loginAsAdmin();
        $bundleRequest = [
            'name' => 'Codeception Product',
            'slug' => 'codeception-product',
            'short_description' => 'This is a short description.'
        ];
        $request = $bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('long description');
    }
    public function tryPostCreateWithoutPrice(UserAuth $I)
    {
        $I->wantTo('Create a bundle without a price');
        $I->loginAsAdmin();
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(422);
        $I->seeResponseContains('price');
    }
}
