<?php
namespace storeSettings;

use Step\Api\UserAuth;
use DB;
use App\Models\Category;
use App\Models\StoreSetting;
use App\Models\Product;
use App\Models\User;

class StoreSettingsCest
{
    public function _before(UserAuth $I)
    {
        $this->category = Category::first();
        $setting = StoreSetting::first();

        $this->categoryHeader = [
            'id' => $this->category->id,
            'header' => 'Codeception'
        ];

        $this->categoryHeaderResponse = [
            'category_id' => $this->category->id,
            'header' => 'Codeception'
        ];

        $this->storeSetting = [
            'key' => $setting->keys()->first()['key'],
            'value' => 'Codeception'
        ];

        $this->storeSettingResponse = [
            'key_id' => $setting->key_id,
            'value' => 'Codeception'
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function createCategoryHeader(UserAuth $I)
    {
        $I->loginAsRep();
        DB::table('category_header')->where('user_id', REP_ID)->delete();
        $I->sendAjaxRequest('POST', 'api/v1/store-settings/category-header', $this->categoryHeader);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->category->toArray());
        $I->seeRecord('category_header', $this->categoryHeaderResponse);
    }

    public function updateCategoryHeader(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', 'api/v1/store-settings/category-header', $this->categoryHeader);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->category->toArray());
        $I->seeRecord('category_header', $this->categoryHeaderResponse);
    }

    public function createStoreSetting(UserAuth $I)
    {
        $I->loginAsRep();
        DB::table('store_settings')->where('user_id', REP_ID)->delete();
        $I->sendAjaxRequest('POST', 'api/v1/store-settings/update', $this->storeSetting);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->storeSettingResponse);
        $I->seeRecord('store_settings', $this->storeSettingResponse);
    }

    public function updateStoreSetting(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', 'api/v1/store-settings/update', $this->storeSetting);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->storeSettingResponse);
        $I->seeRecord('store_settings', $this->storeSettingResponse);
    }

    public function getStoreAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', 'api/v1/store-settings');
        $I->seeResponseCodeIs(200);
        $rep = User::find(REP_ID);
        $I->seeResponseContainsJson($rep->toArray());
        $I->seeResponseContainsJson(Category::where('parent_id', null)->first()->toArray());

        foreach ($rep->storeSettings()->get() as $setting) {
            $I->seeResponseContainsJson([
                $setting->keys()->first()->key => $setting->value
            ]);
        }
    }

    public function getStoreAsAdmin(UserAuth $I)
    {
        $user = User::find(config('site.apex_user_id'));
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/store-settings');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($user->toArray());
        $I->seeResponseContainsJson(Category::where('parent_id', null)->get()->toArray());

        // grab an item name to check if we have item in response
        $inventory = $user->inventories()->where('quantity_available', '>', 0)->get();
        $inventory->load('item.product');
        $inventory = $inventory->sortBy('item.product.name');
        $name = $inventory->first()->item->product->name;

        // See if admin has a product in store from inventory.
        $I->seeResponseContainsJson([
            'name' => $name
        ]);
        foreach ($user->storeSettings()->get() as $setting) {
            $I->seeResponseContainsJson([
                $setting->keys()->first()->key => $setting->value
            ]);
        }
    }
}
