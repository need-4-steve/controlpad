<?php
namespace PublicPageCest;
use \Step\Api\UserAuth;

class PublicPageCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToTest(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Create or update a custom page.');
        $params = [
            'title' => 'Terms and Conditions',
            'slug' => 'terms',
            'content' => '## content'
        ];
        $I->sendAjaxRequest('POST', '/api/v1/pages/create', $params);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($params);
        $I->seeRecord('custom_pages', $params);
    }
}
