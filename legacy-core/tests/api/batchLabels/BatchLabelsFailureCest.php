<?php
namespace batchLabels;

use \Step\Api\UserAuth;
use App\Models\Order;
use App\Models\BatchLabel;
use Carbon\Carbon;

class BatchLabelsFailureCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryCreateBatchWithOverHundredOrders(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = Order::take(101)->pluck('receipt_id')->toArray();
        $I->sendAjaxRequest('POST', '/api/v1/batch/', $request);
        $I->seeResponseCodeIs(422);
    }

    public function tryDeletePurchasedBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['status' => 'PURCHASED', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('DELETE', '/api/v1/batch/'.$batchId);
        $I->seeResponseCodeIs(400);
    }

    public function tryValidateBatchWithNoOrders(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('POST', '/api/v1/batch/validate', ['id' => $batchId]);
        $I->seeResponseCodeIs(400);
    }

    public function tryPurchaseNonValidBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('POST', '/api/v1/batch/purchase', ['id' => $batchId]);
        $I->seeResponseCodeIs(400);
    }

    public function tryPurchaseBatchWithNoOrders(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['status' => 'VALID', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('POST', '/api/v1/batch/purchase', ['id' => $batchId]);
        $I->seeResponseCodeIs(422);
    }
}
