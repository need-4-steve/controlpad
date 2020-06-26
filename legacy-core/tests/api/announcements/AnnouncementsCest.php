<?php

namespace announcements;

use \ApiTester;
use \Step\Api\UserAuth;
use \App\Models\Announcement;

class AnnouncementsCest
{
    public function _before(UserAuth $I)
    {
        $this->announcement = Announcement::latest()->first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    /*
    *THIS NEED FIX SO I HAVE COMMITED IT OUT FOR RIGHT KNOW
    *
    */

    public function tryToGetIndexAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/announcements', ['column' => 'title', 'order' => 'ASC']);
        $I->seeResponseCodeIs(200);
    }

    public function tryToGetIndexAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendGET('/api/v1/announcements/', ['column' => 'title', 'order' => 'ASC']);
        $I->seeResponseCodeIs(200);
    }
}
