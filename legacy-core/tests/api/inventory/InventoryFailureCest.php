<?php
namespace inventory;

use \Step\Api\UserAuth;
use App\Models\Inventory;
use App\Models\User;
use DB;

class InventoryFailureCest
{
    public function _before(UserAuth $I)
    {
        $this->rep = User::whereRoleId(5)->first()->id;
        $this->adminInventory = Inventory::whereUserId(config('site.apex_user_id'))
            ->where('quantity_available', '>', 0)
            ->first();
        $this->repInventory = Inventory::whereUserId($this->rep)
            ->where('quantity_available', '>', 0)
            ->first();
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->wantTo("Get inventory index");
        $request = [
            'search_term' => '',
            'order' => 'ASC',
            'column' => 'id',
            'per_page' => 15
        ];
        $I->sendGET('/api/v1/inventory', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryGetIndexForAdmin(UserAuth $I)
    {
        $I->wantTo("Get inventory index as an admin");
        $I->loginAsRep();
        $request = [
            'search_term' => '',
            'order' => 'ASC',
            'column' => 'id',
            'per_page' => 15
        ];
        $I->sendGET('/api/v1/inventory', $request);
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'per_page' => $request['per_page'],
            'data' => [
                'id' => $this->adminInventory->id,
                'user_id' => config('site.apex_user_id'),
                'item_id' => $this->adminInventory->item->id,
                'product_id' => $this->adminInventory->item->product->id,
                'quantity_available' => $this->adminInventory->quantity_available,
                'size' => $this->adminInventory->item->size,
            ],
        ]);
    }

    public function tryGetIndexForRep(UserAuth $I)
    {
        $I->wantTo("Get inventory index as a rep");
        $I->loginAsAdmin();
        $request = [
            'search_term' => '',
            'order' => 'ASC',
            'column' => 'id',
            'per_page' => 15
        ];
        $I->sendGET('/api/v1/inventory', $request);
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'per_page' => $request['per_page'],
            'data' => [[
                'id' => $this->repInventory->id,
                'user_id' => $this->rep,
                'item_id' => $this->repInventory->item->id,
                'product_id' => $this->repInventory->item->product->id,
                'quantity_available' => $this->repInventory->quantity_available,
                'size' => $this->repInventory->item->size,
            ]],
        ]);
    }

    public function tryGetRep(UserAuth $I)
    {
        $I->wantTo("Get a rep's inventory");
        $I->sendGET('/api/v1/inventory/rep');
        $I->seeResponseCodeIs(401);
    }

    public function tryPostSavePrice(UserAuth $I)
    {
        $I->wantTo("Save a price for a rep's inventory");
        $request = [
            'price' => 999.99,
            'inventory' => [
                'id' => $this->repInventory->id
            ]
        ];
        $I->sendPOST('/api/v1/inventory/save-price', $request);
        $I->seeResponseCodeIs(401);
        $I->dontSeeRecord('prices', [
            'priceable_id' => $this->repInventory->id,
            'priceable_type' => Inventory::class,
            'price' => $request['price'],
            'price_type_id' => 4
        ]);
    }

    public function tryPostSaveQuantity(UserAuth $I)
    {
        $I->wantTo("Save available quantity for admin inventory");
        $request = [
            'quantity' => 9001,
            'inventory' => [
                'id' => $this->adminInventory->id,
                'item_id' => $this->adminInventory->item_id,
            ]
        ];
        $I->sendPOST('/api/v1/inventory/save-quantity', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryPostSaveQuantityRepNotAuth(UserAuth $I)
    {
        $I->wantTo("Try to save quantity when reseller_create_product is false");
        $I->loginAsRep();
        $request = [
            'quantity' => 9001,
            'inventory' => [
                'id' => $this->repInventory->id,
                'item_id' => $this->repInventory->item_id,
                'user_id' => $this->repInventory->user_id
            ]
        ];
        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Reps can create products", "show": false}',
        ]);
        $I->sendPOST('/api/v1/inventory/save-quantity', $request);
        $I->seeResponseCodeIs(403);
    }

    public function tryGetCreateCsvTemplate(UserAuth $I)
    {
        $I->wantTo("Get csv inventory template");
        $I->sendGET('/api/v1/inventory/csv-export');
        $I->seeResponseCodeIs(401);
    }
}
