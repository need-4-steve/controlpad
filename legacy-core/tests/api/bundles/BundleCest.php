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

class BundleCest
{
    public function _before(UserAuth $I)
    {
        $this->bundle = Bundle::where('user_id', config('site.apex_user_id'))->first();
        $this->bundleEdit = Bundle::where('user_id', 106)->first();
        unset($this->bundle->created_at);
        unset($this->bundle->updated_at);

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
            'starter_kit' => false,
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
            'items' => []
        ];
        foreach ($this->items as $item) {
            $this->responseAdditions['items'] += [
                'id' => $item->id,
                'pivot' => [
                    'quantity' => $item->quantity
                ]
            ];
        }

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
        $I->loginAsAdmin();
        $I->wantTo('Get all bundles');
        $I->sendAjaxRequest('GET', '/api/v1/bundles', [
            'category' => 'all',
            'search_term' => '',
            'column' => 'updated_at',
            'order' => 'DESC'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            $this->bundle->toArray()
        ]);
    }

    public function tryPostCreateAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Create a bundle');
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $request['type_id'] = 1;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(200);
        $bundle_id = json_decode($I->grabResponse())->id;
        $response = $this->bundleRequest;
        $response += $this->responseAdditions;
        $response['media'] = [Media::first()->toArray()];
        $response['tags'] = Tag::where('name', 'codeception')->get()->toArray();
        $I->seeResponseContainsJson($response);
    }

    public function tryPostCreateRep(UserAuth $I)
    {
        $I->wantTo('Create a new bundle as rep');
        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Reps can create products", "show": true}',
        ]);
        $I->loginAsRep();
        $I->wantTo('Create a bundle');
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $request['type_id'] = 1;

        $I->sendAjaxRequest('POST', '/api/v1/bundles/create', $request);
        $I->seeResponseCodeIs(200);
        $bundle_id = json_decode($I->grabResponse())->id;
        $response = $this->bundleRequest;
        $response += $this->responseAdditions;
        $response['media'] = [Media::first()->toArray()];
        $response['tags'] = Tag::where('name', 'codeception')->get()->toArray();
        $I->seeResponseContainsJson($response);
    }

    public function tryGetBundlesByRoleCustomer(UserAuth $I)
    {
        $I->wantTo('Get bundles by role Customer');
        $this->bundle->roles()->sync([$this->customer]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
        ]);
    }

    public function tryGetBundlesByRoleRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Get bundles by role Rep');
        $this->bundle->roles()->sync([$this->rep]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([]);
    }

    public function tryGetBundlesByRoleAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Get bundles by role Admin');
        $this->bundle->roles()->sync([$this->admin]);
        $I->sendAjaxRequest('GET', '/api/v1/bundles/bundles-by-role', ['category' => 'all']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
        ]);
    }

    public function tryGetShow(UserAuth $I)
    {
        $I->wantTo('Show a bundle');
        $I->sendAjaxRequest('GET', '/api/v1/bundles/show/'.$this->bundle->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'bundle' => $this->bundle->toArray()
        ]);
    }

    public function tryPutEdit(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Edit a bundle');
        $request = $this->bundleRequest;
        $request['items'] = $this->items;
        $request += $this->requestAdditions;
        $request['wholesale_price'] = $this->price;
        $request['type_id'] = 1;
        $I->sendAjaxRequest('PUT', '/api/v1/bundles/edit/'.$this->bundleEdit->id, $request);
        $I->seeResponseCodeIs(200);
        $bundle_id = $this->bundle->id;
        $response = $this->bundleRequest;
        $response += $this->responseAdditions;
        $response['media'] = [Media::first()->toArray()];
        $response['tags'] = Tag::where('name', 'codeception')->get()->toArray();

        $I->seeResponseContainsJson($response);
    }

    public function tryDeleteDelete(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Delete a bundle');
        $I->sendAjaxRequest('DELETE', '/api/v1/bundles/delete/'.$this->bundle->id);
        $I->seeResponseCodeIs(200);
    }

    public function tryGetStarterKits(UserAuth $I)
    {
        $faker = \Faker\Factory::create();

        $bundle = factory(Bundle::class, 1)->create([
            'name' => 'codecept bundle',
            'slug' => 'codecept-bundle',
            'starter_kit' => 1
        ]);
        $bundle->items()->attach(6, ['quantity' => 1]);
        $I->sendAjaxRequest('GET', '/api/v1/starter-kits');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([$bundle->toArray()]);
    }
}
