<?php
namespace media;

use \Step\Api\UserAuth;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Media;
use DB;
use Carbon\Carbon;

class MediaCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryIndexAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = [
            'status' => 'all',
            'search_term' => '',
            'per_page' => '25',
            'page'  => 1
        ];
        $media = Media::where('user_id', config('site.apex_user_id'))->first();
        $I->sendAjaxRequest('GET', '/api/v1/media/', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => []]);
    }

    public function tryIndexAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $request = [
            'status' => 'all',
            'search_term' => '',
            'per_page' => '25',
            'page'  => 1
        ];
        $media = Media::first();
        $media->user_id = REP_ID;
        $media->save();
        $I->sendAjaxRequest('GET', '/api/v1/media/', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => []]);
    }

    public function tryUserProductImages(UserAuth $I)
    {
        $I->loginAsRep();
        $request = [
            'searchTerm' => '',
            'limit' => '25'
        ];
        $I->sendAjaxRequest('POST', '/api/v1/media/user-product-images', $request);
        $I->seeResponseCodeIs(200);
        $media = auth()->user()->inventories()->where('quantity_available', '>', 10)->first()->item->product->media()->first();
        $I->seeResponseContainsJson([
            'id' => $media->id,
            'url' => $media->url
        ]);
    }

    public function tryCorporateProductImages(UserAuth $I)
    {
        $I->loginAsRep();
        $request = [
            'searchTerm' => '',
            'limit' => '25'
        ];
        $I->sendAjaxRequest('POST', '/api/v1/media/corporate-product-images', $request);
        $I->seeResponseCodeIs(200);
        $inventory = Inventory::where('user_id', config('site.apex_user_id'))->first();
        $media = $inventory->item->product->media()->first();
        $I->seeResponseContainsJson(['id' => $media->id]);
    }

    public function tryCorporateProductImagesWithNoInventory(UserAuth $I)
    {
        $I->loginAsRep();

        $product = Product::first();

        DB::beginTransaction();
        foreach ($product->items as $item) {
            $inventory = $item->inventory->where('user_id', config('site.apex_user_id'))->first();
            $inventory->quantity_available = 0;
            $inventory->expires_at = Carbon::now()->subHours(24)->toDateTimeString();
            $inventory->save();
        }
        DB::commit();

        $request = [
            'searchTerm' => '',
            'limit' => '25'
        ];

        $I->sendAjaxRequest('POST', '/api/v1/media/corporate-product-images', $request);
        $I->seeResponseCodeIs(200);
        $media = $inventory->item->product->media()->first();
        $I->dontSeeResponseContainsJson([
            'id' => $media->id,
            'url' => $media->url
        ]);
    }

    public function tryUpdateAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $media = Media::first();
        $request = [
            'title' => 'Codeception',
            'description' => 'codecept',
            'is_public' => 'true',
        ];
        $I->sendAjaxRequest('PATCH', '/api/v1/media/'.$media->id, $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $media->id,
            'url' => $media->url
        ]);
        $I->seeResponseContainsJson($request);
        $I->seeRecord('media', $request);
    }

    public function tryUpdateAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $media = Media::first();
        $media->user_id = REP_ID;
        $media->save();

        $request = [
            'title' => 'Codeception',
            'description' => 'codecept',
        ];
        $I->sendAjaxRequest('PATCH', '/api/v1/media/'.$media->id, $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $media->id,
            'url' => $media->url
        ]);
        $I->seeResponseContainsJson($request);
        $I->seeRecord('media', $request);
    }

    public function tryToEnableMedia(UserAuth $I)
    {
        $I->loginAsAdmin();
        $media = Media::first();
        $I->sendAjaxRequest('PATCH', '/api/v1/media/enable', ['images' => [$media->id]]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse())[0];
        $I->assertTrue($response->disabled_at === null);
    }

    public function tryToDisableMedia(UserAuth $I)
    {
        $I->loginAsAdmin();
        $media = Media::first();
        $I->sendAjaxRequest('PATCH', '/api/v1/media/disable', ['images' => [$media->id]]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
        $response = json_decode($I->grabResponse())[0];
        $I->assertTrue($response->disabled_at !==  null);
    }

    public function tryToDeleteMedia(UserAuth $I)
    {
        $I->loginAsAdmin();
        $media = Media::first();
        $I->sendAjaxRequest('DELETE', '/api/v1/media/' . $media->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }
}
