<?php
namespace inventory;

use \Step\Api\UserAuth;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Setting;
use DB;

class InventoryCest
{
    public function _before(UserAuth $I)
    {
        $this->rep = User::whereRoleId(5)->first();
        $this->adminInventory = Inventory::whereUserId(config('site.apex_user_id'))
            ->where('quantity_available', '>', 0)
            ->first();
        $this->repInventory = Inventory::whereUserId($this->rep->id)
            ->where('quantity_available', '>', 0)
            ->first();
        $this->reseller_create_product = json_decode(Setting::where('key', 'reseller_create_product')->first()->value);
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryGetIndexForAdmin(UserAuth $I)
    {
        $I->wantTo("Get inventory index as an admin");
        $I->loginAsAdmin();
        $request = [
            'search_term' => $this->adminInventory->item->product->name,
            'order' => 'ASC',
            'column' => 'id',
            'per_page' => 15
        ];
        $I->sendGET('/api/v1/inventory', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'per_page' => $request['per_page'],
            'data' => [[
                'id' => $this->adminInventory->id,
                'user_id' => config('site.apex_user_id'),
                'item_id' => $this->adminInventory->item->id,
                'product_id' => $this->adminInventory->item->product->id,
                'quantity_available' => $this->adminInventory->quantity_available,
                'size' => $this->adminInventory->item->size,
            ]],
        ]);

        $this->validateInventoryResponse($I);
    }

    public function tryGetIndexForRep(UserAuth $I)
    {
        $I->wantTo("Get inventory index as a rep");
        $I->loginAsRep();
        $request = [
            'search_term' => $this->repInventory->item->product->name,
            'order' => 'ASC',
            'column' => 'id',
            'per_page' => 15
        ];
        $I->sendGET('/api/v1/inventory', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'per_page' => $request['per_page'],
            'data' => [[
                'id' => $this->repInventory->id,
                'user_id' => $this->rep->id,
                'item_id' => $this->repInventory->item->id,
                'product_id' => $this->repInventory->item->product->id,
                'quantity_available' => $this->repInventory->quantity_available,
                'size' => $this->repInventory->item->size,
            ]],
        ]);

        $this->validateInventoryResponse($I);
    }

    public function tryGetRep(UserAuth $I)
    {
        $I->wantTo("Get a rep's inventory");
        $I->loginAsRep();
        $I->sendGET('/api/v1/inventory/rep');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            0 => [
                'id' => $this->repInventory->id,
                'user_id' => $this->rep->id,
                'item_id' => $this->repInventory->item->id,
                'quantity_available' => $this->repInventory->quantity_available,
            ]
        ]);
    }

    public function tryPostSavePrice(UserAuth $I)
    {
        $I->wantTo("Save a price for a rep's inventory");
        $I->loginAsRep();
        $request = [
            'rep_price' => 999.99,
            'id' => $this->repInventory->id
        ];

        $I->sendPOST('/api/v1/inventory/save-price', $request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('prices', [
            'priceable_id' => $this->repInventory->id,
            'priceable_type' => Inventory::class,
            'price' => $request['rep_price'],
            'price_type_id' => 4
        ]);
    }

    public function tryPostSaveQuantity(UserAuth $I)
    {
        $I->wantTo("Save available quantity for admin inventory");
        $I->loginAsAdmin();
        $request = [
            'quantity' => 9001,
            'inventory' => [
                'id' => $this->adminInventory->id,
                'item_id' => $this->adminInventory->item_id,
            ]
        ];
        $I->sendPOST('/api/v1/inventory/save-quantity', $request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('inventories', [
            'id' => $this->adminInventory->id,
            'item_id' => $this->adminInventory->item->id,
            'quantity_available' => $request['quantity'],
            'user_id' => config('site.apex_user_id')
        ]);
    }

    public function tryPostSaveQuantityRep(UserAuth $I)
    {
        $I->wantTo("Save available quantity for Rep inventory");
        $I->loginAsRep();
        $request = [
            'quantity' => 9001,
            'inventory' => [
                'id' => $this->repInventory->id,
                'item_id' => $this->repInventory->item_id,
            ]
        ];

        // Temporarly change a product user_id to the rep's user_id
        $inventory = $this->repInventory;
        $inventory->load('item.product');
        $product = $inventory->item->product;
        $product->user_id = REP_ID;
        $product->save();

        DB::table('settings')->where('key', 'reseller_create_product')->update([
            'value' => '{"value": "Reps can create products", "show": true}',
        ]);
        $I->sendPOST('/api/v1/inventory/save-quantity', $request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('inventories', [
            'id' => $this->repInventory->id,
            'item_id' => $this->repInventory->item->id,
            'quantity_available' => $request['quantity'],
            'user_id' => $this->repInventory->user_id
        ]);
    }

    public function tryGetCheckAvailability(UserAuth $I)
    {
        $I->wantTo("Check availability of inventory");
        $quantity = $this->adminInventory->where('quantity_available', '>', 1)->first();
        $request = [
            'item_id' => $this->adminInventory->item->id,
            'quantity' => $quantity->quantity_available * 5000
        ];
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => true,
        ]);
        $request['quantity'] = 1;
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
        ]);
    }
    public function tryGetCheckAvailabilityFromRep(UserAuth $I)
    {
        $I->wantTo('Check Item availability from a reps store as Representative');
        $this->rep->seller_type_id = 2;
        session()->put('store_owner', $this->rep);
        $request = [
            'item_id' => $this->repInventory->item->id,
            'quantity' => $this->repInventory->quantity_available + 1
        ];
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => true,
        ]);

        $request['quantity'] = 1;
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
        ]);
    }
    public function tryGetCheckAvailabilityFromAffiliate(UserAuth $I)
    {
        $I->wantTo('Check Item availability from a reps store as Affiliate');
        $this->rep->seller_type_id = 1;
        session()->put('store_owner', $this->rep);
        $request = [
            'item_id' => $this->adminInventory->item->id,
            'quantity' => $this->adminInventory->quantity_available + 1
        ];
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => true,
        ]);

        $request['quantity'] = 1;
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
        ]);
    }
    public function tryGetCheckAvailabilityFromAHybrid(UserAuth $I)
    {
        $I->wantTo('Check Item availability from a reps store as Hybrid');
        $this->rep->seller_type_id = 3;
        session()->put('store_owner', $this->rep);
        $admin = Inventory::whereUserId(config('site.apex_user_id'))
            ->where('item_id', $this->repInventory->item->id)
            ->first()->quantity_available;
        $rep = Inventory::whereUserId($this->rep->id)
            ->where('item_id', $this->repInventory->item->id)
            ->first()->quantity_available;

        $request = [
            'item_id' => $this->repInventory->item->id,
            'quantity' => $admin + $rep + 1
        ];
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => true,
        ]);

        $request['quantity'] = 1;
        $I->sendGET('/api/v1/inventory/check-availability', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
        ]);
    }

    public function tryGetCreateCsvTemplate(UserAuth $I)
    {
        $I->wantTo("Get csv inventory template");
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/inventory/csv-export');
        $I->disableEvents();
        $I->seeResponseCodeIs(200);
    }

    private function validateInventoryResponse($I)
    {
        // Check response structure
        $I->seeResponseMatchesJsonType([
            'data' => [
                '0' => [
                    'id' => 'integer',
                    'item_id' => 'integer',
                    'user_id' => 'integer',
                    'quantity_available' => 'integer',
                    'product_id' => 'integer',
                    'product_user_id' => 'integer',
                    'size' => 'string|null',
                    'print' => 'string|null',
                    'custom_sku' => 'string|null',
                    'manufacturer_sku' => 'string',
                    'name' => 'string',
                    'disabled_at' => 'string|null',
                    'expires_at' => 'string|null',
                    // Mysql will automatically cast a decmial to be a string if the field is nullable (e.g. price)
                    'wholesale_price' => 'string',
                    'premium_price' => 'string',
                    'msrp' => 'string',
                    'default_media' => [
                        'url' => 'string|null',
                        'url_xxs' => 'string|null',
                        'url_xs' => 'string|null',
                        'url_lg' => 'string|null'
                    ]
                ]
            ]
        ]);
    }
}
