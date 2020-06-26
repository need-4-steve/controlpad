<?php
namespace userSettings;

use \ApiTester;
use \Step\Api\UserAuth;

use App\Models\User;
use App\Models\Role;
use App\Models\UserSetting;

class UserSettingsCest
{
    public function _before(UserAuth $I)
    {
        $this->repUser = User::where('email', 'rep@controlpad.com')->first();
        $this->adminUser = User::where('email', 'admin@controlpad.com')->first();
    }

    public function _after(UserAuth $I)
    {
    }

    // get a rep's settings as rep
    public function tryToGetRepSettingsAsRep(UserAuth $I)
    {
        $I->wantTo('Get rep settings as that rep');
        $I->loginAsRep();
        $I->sendGET('/api/v1/user-settings/' . $this->repUser->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['user_id' => $this->repUser->id]);
    }

    // get a rep's settings as admin
    public function tryToGetRepSettingsAsAdmin(UserAuth $I)
    {
        $I->wantTo('Get rep settings as an admin');
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/user-settings/' . $this->repUser->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['user_id' => $this->repUser->id]);
    }

    // update a rep's settings as a rep
    public function tryToUpdateRepSettingsAsRep(UserAuth $I)
    {
        // make usersettings from factory
        $userSettings = factory(UserSetting::class, 1)
                            ->make(['user_id' => $this->repUser->id])
                            ->toArray();

        $I->wantTo('Update rep settings as that rep');
        $I->loginAsRep();
        $I->sendPUT('/api/v1/user-settings/update/', $userSettings);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['user_id' => $this->repUser->id]);
    }

    // update a rep's settings as an admin
    public function tryToUpdateRepSettingsAsAdmin(UserAuth $I)
    {
        // make usersettings from factory
        $userSettings = factory(UserSetting::class, 1)
                            ->make(['user_id' => $this->repUser->id])
                            ->toArray();

        $I->wantTo('Update rep settings as an admin');
        $I->loginAsAdmin();
        $I->sendPUT('/api/v1/user-settings/update/', $userSettings);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['user_id' => $this->repUser->id]);
    }
}
