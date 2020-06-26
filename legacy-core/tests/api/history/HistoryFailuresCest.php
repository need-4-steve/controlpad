<?php
namespace history;

use \ApiTester;
use \App\Models\History;
use \Step\Api\UserAuth;

class HistoryFailuresCest
{
    public function _before(ApiTester $I)
    {
        $this->history = History::first();
        $this->model = substr($this->history->historable_type, 11);
        $this->modelLower = strtolower($this->model);
    }

    public function _after(ApiTester $I)
    {
    }

    //test
    public function tryToGetIndexAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/history');
        $I->seeResponseCodeIs(403);
    }

    public function tryToGetModelAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/history/model/'.$this->modelLower);
        $I->seeResponseCodeIs(403);
    }

    public function tryToGetIdAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/history/id/'. $this->modelLower . '/' . $this->history->historable_id);
        $I->seeResponseCodeIs(403);
    }
}
