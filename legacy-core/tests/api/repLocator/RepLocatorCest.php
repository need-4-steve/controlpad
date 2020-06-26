<?php
namespace repLocator;

use \ApiTester;
use \Step\Api\UserAuth;

class RepLocatorCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToSearch(UserAuth $I)
    {
        // COMMENTING THIS TEST OUT BECAUSE THE ENDPOINT CANNOT BE CALLED ON TESTS
        // $I->sendAjaxRequest('GET', 'api/v1/rep-locator/search/59101/500');
        // $I->seeResponseCodeIs(200);
        // $I->seeResponseContainsJson();
    }
}
