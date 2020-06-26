<?php
namespace cart;

use Step\Api\UserAuth;

use App\Models\Item;
use App\Models\Cart;
use App\Models\Bundle;
use App\Models\Product;

class CartApiCest
{
    public function _before(UserAuth $I)
    {
        $this->item = Item::first();
        $this->bundle = Bundle::first();
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryGetIndex(UserAuth $I)
    {
        $I->wantTo('Get the Cart');
        $I->sendAjaxRequest('POST', '/api/v1/cart/show', []);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [
               'lines' => []
            ]
        ]);
        $response = json_decode($I->grabResponse());
        $I->seeRecord('carts', ['uid' => $response->data->uid]);
    }

    public function tryDeleteCart(UserAuth $I)
    {
        $I->wantTo('Delete a cartline');
        $I->sendAjaxRequest('POST', '/api/v1/cartlines/wholesale', [[
            'item_id' => $this->item->id,
            'quantity' => 1
        ]]);
        $I->seeResponseCodeIs(200);
        $I->sendAjaxRequest('POST', '/api/v1/cart/show', []);

        $response = json_decode($I->grabResponse())->data;

        $cartline = $response->lines[0];

        $I->sendAjaxRequest('DELETE', '/api/v1/cart/cartline/'.$cartline->id);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('cartlines', ['id' => $cartline->id]);
    }

    public function tryAddBundle(UserAuth $I)
    {
        $I->wantTo('Add bundle to the cart');
        $I->sendAjaxRequest('PUT', '/api/v1/cart/bundle', [
            'bundle_id' => $this->item->id,
            'quantity' => 2
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
        $I->seeRecord('bundle_cart', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
    }

    /**
     * @depends tryAddBundle
     */
    public function tryUpdateBundle(UserAuth $I)
    {
        $I->wantTo('Update bundle quantity to the cart');
        $I->sendAjaxRequest('PUT', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
        $I->sendAjaxRequest('PATCH', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
        $I->seeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
        $I->seeRecord('bundle_cart', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
        $response = json_decode($I->grabResponse());
        $I->assertTrue(count($response->cart->bundles) === 1);
    }

    /**
     * @depends tryAddBundle
     */
    public function tryAddDuplicateBundle(UserAuth $I)
    {
        $I->wantTo('Add a duplicate bundle to the cart');
        $I->sendAjaxRequest('PUT', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);

        $I->sendAjaxRequest('PUT', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 2
        ]);
        $I->seeResponseContainsJson([
             'message' => 'Updated Bundle on the Cart'
        ]);
        $I->seeRecord('bundle_cart', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 3
        ]);
        $response = json_decode($I->grabResponse());
        $I->assertTrue(count($response->cart->bundles) === 1);
    }


    public function tryRemoveBundle(UserAuth $I)
    {
        $I->wantTo('Remove bundle from the cart');
        $I->sendAjaxRequest('PUT', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);

        $I->sendAjaxRequest('PATCH', '/api/v1/cart/bundle', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 0
        ]);

        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
        $I->dontSeeRecord('bundle_cart', [
            'bundle_id' => $this->bundle->id,
            'quantity' => 1
        ]);
    }
}
