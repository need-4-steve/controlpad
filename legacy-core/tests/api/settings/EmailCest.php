<?php
namespace settings;
use \ApiTester;
use \Step\Api\UserAuth;
use App\Models\SettingEmail;

class EmailCest
{
    public function _before(UserAuth $I)
    {
        $this->email = SettingEmail::first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToGetIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/emails/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            ]);
    }

    public function tryToShowByUser(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/emails/show/'.$this->email->user_id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            ]);
    }

    public function tryToShowById(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/emails/show_id/'.$this->email->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            ]);
    }

    public function tryToCreate(UserAuth $I)
    {
         $I->loginAsAdmin();
         $I->sendPOST('/api/v1/emails/create', [
                'key' => 'test',
                'value' => 'the value test'
            ]);
         $I->seeResponseCodeIs(200);
         $I->seeResponseContainsJson(['key' => 'test']);
    }

    public function tryToUpdate(UserAuth $I)
    {
         $I->loginAsAdmin();
         $I->sendPUT('/api/v1/emails/update/'. $this->email->id, [
            'value' => 'this is a new value'
            ]);
         $I->seeResponseCodeIs(200);
         $I->seeResponseContainsJson([
            'value' => 'this is a new value'
            ]);
    }
}
