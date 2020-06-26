<?php
namespace states;

use Step\Api\UserAuth;

class StateCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryIndex(UserAuth $I)
    {
        $I->wantTo('Get all states');
        $I->sendGET('/api/v1/states');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            [
                'abbr' => "AL",
                'full_name' => "Alabama",
            ],
            [
                'abbr' => "UT",
                'full_name' => "Utah",
            ],
            [
                'abbr' => "WY",
                'full_name' => "Wyoming",
            ],
        ]);
    }
}
