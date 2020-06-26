<?php
namespace media;

use \Step\Api\UserAuth;
use App\Models\Media;

class MediaFailureCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryIndexAsCustomer(UserAuth $I)
    {
        $request = [
            'searchTerm' => '',
            'limit' => '25'
        ];
        $I->sendAjaxRequest('GET', '/api/v1/media/', $request);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryUserProductImagesAsCustomer(UserAuth $I)
    {
        $request = [
            'searchTerm' => '',
            'limit' => '25'
        ];
        $I->sendAjaxRequest('POST', '/api/v1/media/user-product-images', $request);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryUpdateAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $media = Media::where('user_id', '!=', REP_ID)->first();

        $request = [
            'title' => 'Codeception',
            'description' => 'codecept',
            'images' => [
                $media->id
            ]
        ];
        $I->sendAjaxRequest('PATCH', '/api/v1/media/'.$media->id, $request);
        $I->dontSeeResponseCodeIs(200);
        $I->seeResponseCodeIs(403);
    }

    public function tryToEnableMediaAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $media = Media::first();
        $I->sendAjaxRequest('PATCH', '/api/v1/media/enable', ['images' => [$media->id]]);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryToDisableMediaRep(UserAuth $I)
    {
        $I->loginAsRep();
        $media = Media::first();
        $I->sendAjaxRequest('PATCH', '/api/v1/media/disable', ['images' => [$media->id]]);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryToDeleteMediaAsCustomer(UserAuth $I)
    {
        $I->wantTo('Try to delete media while not logged in.');
        $media = Media::first();
        $I->sendAjaxRequest('DELETE', '/api/v1/media/' . $media->id);
        $I->seeResponseCodeIs(401);
    }
}
