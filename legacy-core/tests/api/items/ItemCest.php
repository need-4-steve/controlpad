<?php
namespace item;

use \ApiTester;
use \App\Models\Item;
use \Step\Api\UserAuth;
use \App\Models\Inventory;
use DB;

class ItemCest
{
    private $item;

    public function _before(UserAuth $I)
    {
        $this->item = Item::has('inventory')->first();
        $this->RepItem = Item::with('inventory')->whereHas('inventory', function ($query) {
                $query->where('user_id', 106);
        })->first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetItem(UserAuth $I)
    {
        $I->sendAjaxRequest('GET', '/api/v1/items/'. $this->item->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $this->item->id,
            'product_id' => $this->item->product_id,
            'size' => $this->item->size
        ]);
    }

    public function tryGetAllItemAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/items');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $this->item->id,
            'product_id' => $this->item->product_id,
            'size' => $this->item->size
        ]);
    }
    public function tryGetAllItemRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/items');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $this->RepItem->id,
            'product_id' => $this->RepItem->product_id,
            'size' => $this->RepItem->size
        ]);
    }

    public function tryDeleteItemWithInventory(UserAuth $I)
    {
        $I->sendAjaxRequest('DELETE', 'api/v1/items/'. $this->item->id);
        $I->seeResponseCodeIs(400);
    }

    public function tryDeleteItemWithOutInventory(UserAuth $I)
    {
        $inventories = Inventory::where('item_id', $this->item->id)
            ->update(['quantity_available' => 0, 'quantity_staged' => 0]);
        $I->sendAjaxRequest('DELETE', 'api/v1/items/'. $this->item->id);
        $I->seeResponseCodeIs(200);
    }
}
