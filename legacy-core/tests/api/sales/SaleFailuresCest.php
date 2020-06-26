<?php
namespace sales;

use \ApiTester;
use \Step\Api\UserAuth;

class SaleFailuresCest
{
    public function _before(ApiTester $I)
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

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryGetIndexNotLogin(UserAuth $I)
    {
        $I->sendAjaxRequest('GET', '/api/v1/sales', $this->request);
        $I->seeResponseCodeIs(401);
    }
}
