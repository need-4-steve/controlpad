<?php
namespace eWallet;

use \ApiTester;
use \Step\Api\UserAuth;

class EWalletCest
{
    public function _before(ApiTester $I)
    {
        $this->request = [
            'order'     => 'ASC',
            'column'    => 'name',
            'per_page'  => 15,
            'page'      => 1
        ];
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToGetSalesTax(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/sales-tax', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetSalesTaxRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/sales-tax', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetDashboardReport(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/dashboard-report');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetDashboardReportRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/dashboard-report');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToPostWithdraw(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('POST', 'api/v1/ewallet/withdraw', ['total' => 10.00, 'source' => 'Company']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToPostWithdrawRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', 'api/v1/ewallet/withdraw', ['total' => 10.00, 'source' => 'Rep']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetTransactions(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/transactions', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetTransactionsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', 'api/v1/ewallet/transactions', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }
}
