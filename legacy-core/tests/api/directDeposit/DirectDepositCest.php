<?php
namespace directDeposit;

use \ApiTester;
use \Step\Api\UserAuth;
use \Carbon\Carbon;

class DirectDepositCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToGetBatch(UserAuth $I)
    {

        $data = [
            'page' => 1,
            'per_page' => 25,
            'start_date' => Carbon::now()->subDay(30)->toDateTimeString(),
            'end_date' => Carbon::now()->toDateTimeString(),
            'submitted' => 'true'
        ];
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/direct-deposit/batch-index', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToBatchSubmitted(UserAuth $I)
    {
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('POST', 'api/v1/direct-deposit/batch-submit/106');
        $I->seeResponseCodeIs(200);
    }

    public function tryToGetUserAccoutIndex(UserAuth $I)
    {
        $data = [
            'page' => 1,
            'per_page' => 25
        ];
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/direct-deposit/account-index', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetDetail(UserAuth $I)
    {
        $data = [
            'page' => 1,
            'per_page' => 25,
           'start_date' => Carbon::now()->subDay(30)->toDateTimeString(),
            'end_date' => Carbon::now()->toDateTimeString(),
            'paymentFileId' => 15,
        ];
        $I->haveRecord('orders', [
            'id'=> 996,
            'transaction_id' => '1bovGAv5g8fuXx',
            'customer_id' => 106,
            'store_owner_user_id' => 1,
            'receipt_id' => 'AJXFWI-992',
            ]);
        $I->loginAsSuperAdmin();
        $I->sendAjaxRequest('GET', 'api/v1/direct-deposit/detail', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetDownload(UserAuth $I)
    {
    }
}
