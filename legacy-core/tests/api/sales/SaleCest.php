<?php
namespace sales;

use \ApiTester;
use \Step\Api\UserAuth;

class SaleCest
{
    public function _before(UserAuth $I)
    {
        $this->request =[
            "column" => "id",
            "end_date" => "2016-09-20T06:00:00.000Z",
            "order" => "asc",
            "page" => "1",
            "per_page" => "15",
            "search_term" => "",
            "start_date" => "2016-06-22T06:00:00.000Z"
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetIndexAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/sales', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'per_page' => 15
            ]);
    }
    public function tryGetIndexAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/sales', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'per_page' => 15
            ]);
    }
}
