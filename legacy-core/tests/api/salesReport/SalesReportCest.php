<?php
namespace salesReport;

use \Step\Api\UserAuth;
use App\Models\Order;
use Carbon\Carbon;

class SalesReportCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    public function getRepIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/rep', ['start_date' => '2015-01-01 00:00:00', 'column' => 'users.id', 'order' => 'ASC']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
    }

    public function getRep(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/rep/'.REP_ID, [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
        $I->seeResponseContainsJson(['store_owner_user_id' => REP_ID]);
    }

    public function getRepTotal(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/rep/total', ['start_date' => '2015-01-01 00:00:00']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response > 0);
    }

    public function getIndividualRepTotal(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/rep/total/'.REP_ID, ['start_date' => '2015-01-01 00:00:00']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response > 0);
    }

    public function getCorpIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/corp', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
    }
	
	public function getCustIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/cust', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
    }

    public function getCorpRetail(UserAuth $I)
    {
        $I->haveRecord('orders', [
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 2,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/corp/retail', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
        $I->seeResponseContainsJson(['type_id' => 2]);
        $I->dontSeeResponseContainsJson(['type_id' => 1]);
    }

    public function getCorpWholesale(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/corp/wholesale', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
        $I->seeResponseContainsJson(['type_id' => 1]);
        $I->dontSeeResponseContainsJson(['type_id' => 2]);
    }

    public function getCorpTotal(UserAuth $I)
    {
        $I->haveRecord('orders', [
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 2,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/corp/total', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->retail_sales > 0);
        $I->assertTrue($response->wholesale_sales > 0);
    }

    public function getTaxTotal(UserAuth $I)
    {
        $I->haveRecord('orders', [
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 2,
            'total_tax' => 9.91,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->haveRecord('orders', [
            'store_owner_user_id' => REP_ID,
            'customer_id' => 106,
            'type_id' => 3,
            'total_tax' => 9.92,
            'total_price' => 101,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->haveRecord('orders', [
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 6,
            'total_tax' => 9.93,
            'total_price' => 102,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/tax/total', ['start_date' => '2015-01-01 00:00:00']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->corporate_taxes > 0);
        $I->assertTrue($response->rep_taxes > 0);
        $I->assertTrue($response->fbc_taxes > 0);
    }

    public function getFbcIndex(UserAuth $I)
    {
        $order = $I->haveRecord('orders', [
            'id' => 90000,
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 6,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->haveRecord('orderlines', [
            'order_id' => 90000,
            'created_at' => date('Y-m-d H:i:s'),
            'price' => 5.99,
            'quantity' => 2,
            'inventory_owner_id' => REP_ID
        ]);

        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/fbc', [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString(),
            'column' => 'users.id',
            'order' => 'ASC']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
    }

    public function getFbcUser(UserAuth $I)
    {
        $order = $I->haveRecord('orders', [
            'id' => 90000,
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 6,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->haveRecord('orderlines', [
            'order_id' => 90000,
            'created_at' => date('Y-m-d H:i:s'),
            'price' => 5.99,
            'quantity' => 2,
            'inventory_owner_id' => REP_ID
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/fbc/'.REP_ID, [
            'start_date' => Carbon::now()->startOfDecade()->toDateTimeString(),
            'end_date' => Carbon::now()->endOfDay()->toDateTimeString()]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response->total > 0);
        $I->seeResponseContainsJson(['inventory_owner_id' => REP_ID]);
        $I->seeResponseContainsJson(['store_owner_user_id' => config('site.apex_user_id')]);
    }

    public function getFbcTotal(UserAuth $I)
    {
        $order = $I->haveRecord('orders', [
            'id' => 90000,
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id' => 106,
            'type_id' => 6,
            'total_price' => 100,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $I->haveRecord('orderlines', [
            'order_id' => 90000,
            'created_at' => date('Y-m-d H:i:s'),
            'price' => 5.99,
            'quantity' => 2,
            'inventory_owner_id' => REP_ID
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/report/sales/rep/total', ['start_date' => '2015-01-01 00:00:00']);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse());
        $I->assertTrue($response > 0);
    }
}
