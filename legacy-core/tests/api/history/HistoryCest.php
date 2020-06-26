<?php
namespace history;

use \ApiTester;
use \App\Models\History;
use \Step\Api\UserAuth;

class HistoryCest
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

    // tests
    public function tryToGetIndex(UserAuth $I)
    {
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/history');
        $I->seeResponseCodeIs(200);
    }

    public function tryToGetModel(UserAuth $I)
    {
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/history/model/'.$this->modelLower);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'historable_type' => $this->model
            ]);
    }

    public function tryToGetId(UserAuth $I)
    {
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/history/id/'. $this->modelLower . '/' . $this->history->historable_id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
                'historable_type' => $this->model
            ]);
    }
}
