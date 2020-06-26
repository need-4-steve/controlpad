<?php
namespace roles;

use ApiTester;
use Step\Api\UserAuth;
use App\Models\Roles;

class RoleCest
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
        $I->wantTo('Get all roles');
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/roles');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            ['id' => 3, 'name' => 'Customer'],
            ['id' => 5, 'name' => 'Rep'],
            ['id' => 7, 'name' => 'Admin'],
            ['id' => 8, 'name' => 'Superadmin']
        ]);
    }
}
