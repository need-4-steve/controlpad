<?php

namespace users;

use \ApiTester;
use \Step\Api\UserAuth;

class UserApiCest
{
    public function _before(UserAuth $I)
    {
        $this->newUser = [
            'first_name' => 'testUser',
            'last_name' => 'lastName',
            'email' => 'email@m.co',
            'role_id' => 5
        ];

        $this->apexUserId = ['id' => config('site.apex_user_id')];
        $this->repId = ['id' => 106];
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryToGetUserIndex(UserAuth $I)
    {
        $I->wantTo('Get an index of users as an admin.');
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/user');
        $I->seeResponseCodeIs(200);
    }
	public function tryToGetCustIndex(UserAuth $I)
    {
        $I->wantTo('Get an index of users as an admin.');
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/user');
        $I->seeResponseCodeIs(200);
    }

    public function tryToGetUserIndexWithoutAdmin(UserAuth $I)
    {
        $I->wantTo('Get an index of users without being logged in.');
        $I->sendGET('/api/v1/user');
        $I->seeResponseCodeIs(401);
    }

    public function tryToGetUserIndexAsRep(UserAuth $I)
    {
        $I->wantTo('Get an index of users as a rep.');
        $I->loginAsRep();
        $I->sendGET('/api/v1/user');
        $I->seeResponseCodeIs(403);
    }

    public function tryToGetNames(UserAuth $I)
    {
        $I->wantTo('Get a list of user names');
        $I->loginAsAdmin();
        $I->sendGET('api/v1/user/names');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->apexUserId);
    }

    public function tryToGetNamesAsRep(UserAuth $I)
    {
        $I->wantTo('Get a list of user names as a rep');
        $I->loginAsRep();
        $I->sendGET('api/v1/user/names');
        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson($this->apexUserId);
    }

    public function tryToGetNamesWithoutLogin(UserAuth $I)
    {
        $I->wantTo('Get a list of user names without being logged in');
        $I->sendGET('api/v1/user/names');
        $I->seeResponseCodeIs(401);
    }

    public function tryToShowUserAsAdmin(UserAuth $I)
    {
        $I->wantTo('Show data about a user');
        $I->loginAsAdmin();
        $I->sendGET('api/v1/user/show/' . config('site.apex_user_id'));
        $I->seeResponseCodeIs(200);
    }

    public function tryToShowUserAsRep(UserAuth $I)
    {
        $I->wantTo('Show data about a user as a rep');
        $I->loginAsRep();
        $I->sendGET('api/v1/user/show/'. $this->repId['id']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->repId);
    }
    public function tryToShowUser(UserAuth $I)
    {
        $I->wantTo('Show data about a user for admin');
        $I->loginAsRep();
        $I->sendGET('api/v1/user/show/' . config('site.apex_user_id'));
        $I->seeResponseCodeIs(403);
    }

    public function tryToGetListOfReps(UserAuth $I)
    {
        $I->wantTo('Show a list of the rep users');
        $I->loginAsAdmin();
        $I->sendGET('api/v1/user/reps');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->repId);
    }

    public function tryToGetListOfRepsWithoutLogin(UserAuth $I)
    {
        $I->wantTo('Show a list of the rep users');
        $I->loginAsRep();
        $I->sendGET('api/v1/user/reps');
        $I->seeResponseCodeIs(403);
    }

    public function tryToUpdateJoinDateAsSuperAdmin(UserAuth $I)
    {
        $I->wantTo('Update user join date as SuperAdmin');
        $I->loginAsSuperAdmin();
        $params = [
            'user_id' => $this->repId['id'],
            'join_date' => '2017-12-01'
        ];
        $I->sendPOST('/api/v1/user/edit-join-date', $params);
        $I->seeResponseCodeIs(200);
    }

    public function tryToUpdateJoinDateAsAdmin(UserAuth $I)
    {
        $I->wantTo('Update user join date as Admin');
        $I->loginAsAdmin();
        $params = [
            'user_id' => $this->repId['id'],
            'join_date' => '2017-12-01'
        ];
        $I->sendPOST('/api/v1/user/edit-join-date', $params);
        $I->seeResponseCodeIs(403);
    }
}
