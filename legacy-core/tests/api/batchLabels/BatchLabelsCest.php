<?php
namespace batchLabels;

use \Step\Api\UserAuth;
use App\Models\Order;
use App\Models\BatchLabel;
use Carbon\Carbon;

class BatchLabelsCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryCreateBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = Order::take(2)->pluck('receipt_id')->toArray();
        $I->sendAjaxRequest('POST', '/api/v1/batch/', $request);
        $I->seeResponseCodeIs(200);
        foreach ($request as $receiptId) {
            $I->seeResponseContainsJson(['receipt_id' => $receiptId]);
        }
    }

    public function tryShowBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('GET', '/api/v1/batch/'.$batchId);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $batchId]);
    }

    public function tryBatchIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('GET', '/api/v1/batch/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $batchId]);
    }

    public function tryUpdateBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $request = [
            'id' => $batchId,
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz'
        ];
        $I->sendAjaxRequest('POST', '/api/v1/batch/update', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($request);
    }

    public function tryDeleteBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $I->sendAjaxRequest('DELETE', '/api/v1/batch/'.$batchId);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('batch_labels', ['id' => $batchId]);
    }

    public function tryUpdateBatchShipment(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $shipmentId = $I->haveRecord('batch_shipments', ['batch_label_id' => $batchId, 'order_id' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        $batch = BatchLabel::find($batchId);
        foreach ($batch->shipments() as $shipment) {
            $request = [
                'id' => $shipment->id,
                'parcel_template_id' => 20,
                'carrier_id' => 18,
                'service_level_id' => 3,
                'weight' => 1,
                'mass_unit' => 'oz'
            ];
            $I->sendAjaxRequest('POST', '/api/v1/batch/shipment', $request);
            $I->seeResponseCodeIs(200);
            $I->seeResponseContainsJson($request);
        }
    }

    public function tryValidateBatch(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', [
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $shipmentId = $I->haveRecord('batch_shipments', [
            'batch_label_id' => $batchId,
            'order_id' => 1,
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $shipmentId = $I->haveRecord('batch_shipments', [
            'batch_label_id' => $batchId,
            'order_id' => 2,
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $request = ['id' => $batchId];
        $I->sendAjaxRequest('POST', '/api/v1/batch/validate', $request);
        $I->seeResponseCodeIs(200);
        $batch = json_decode($I->grabResponse());
        $I->assertTrue(($batch->status === 'VALID' || $batch->status === 'VALIDATING'));
    }

    public function tryDeleteBatchShipment(UserAuth $I)
    {
        $I->loginAsAdmin();
        $batchId = $I->haveRecord('batch_labels', [
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $shipmentId = $I->haveRecord('batch_shipments', [
            'batch_label_id' => $batchId,
            'order_id' => 1,
            'parcel_template_id' => 20,
            'carrier_id' => 18,
            'service_level_id' => 3,
            'weight' => 1,
            'mass_unit' => 'oz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $I->sendAjaxRequest('DELETE', '/api/v1/batch/shipment/'.$shipmentId);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('batch_shipments', ['id' => $shipmentId]);
    }
}
