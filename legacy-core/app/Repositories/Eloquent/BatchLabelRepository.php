<?php

namespace App\Repositories\Eloquent;

use App\Models\BatchLabel;
use App\Models\BatchLabelShipment;
use App\Services\Shippo\BatchLabelService;
use App\Models\Order;
use DB;

class BatchLabelRepository
{
    /**
     * BatchLabelRepository constructor.
     *
     * @param BatchLabelService $batchLabelService
     */
    public function __construct(
        BatchLabelService $batchLabelService
    ) {
        $this->batchLabelService = $batchLabelService;
    }

    /**
     * Creates an instance of a batch and attaches orders to the batch as shipments.
     *
     * @param array $orderReceiptIds
     * @return BatchLabel $batch
     */
    public function create($orderReceiptIds)
    {
        $batch = BatchLabel::create();
        $orderIds = Order::whereIn('receipt_id', $orderReceiptIds)->get();
        $batch->orders()->sync($orderIds);
        $batch->load('shipments.order');
        return $batch;
    }

    /**
     * Deletes a batch that has not been payed for.
     *
     * @param  int $id
     * @return BatchLabel $batch
     */
    public function delete($id)
    {
        $batch = $this->find($id);
        if ($batch->status === 'PURCHASED' or $batch->status === 'PURCHASING') {
            return ['error' => 'A batch that has been purchased cannot be deleted.'];
        }
        $batch->orders()->detach();
        $batch->delete();
        return $batch;
    }

    /**
     * Deletes a shipment/order within a batch.
     *
     * @param  int $id This is the id of the shipment.
     * @return BatchLabelShipment
     */
    public function deleteShipment($id)
    {
        return BatchLabelShipment::destroy($id);
    }

    /**
     * Finds a certain instance of a BatchLabel
     *
     * @param  int $id
     * @param  array $eagerLoad
     * @return BatchLabelShipment
     */
    public function find($id, $eagerLoad = [])
    {
        $batch = BatchLabel::where('id', $id)->with($eagerLoad)->first();
        if (isset($batch->shippo_id)) {
            $this->batchLabelService->get($batch);
        }
        return $batch;
    }


    /**
     * Finds a certain instance of a BatchLabelShipment
     *
     * @param  int $id
     * @return BatchLabelShipment $shipment
     */
    public function findShipment($id)
    {
        $shipment = BatchLabelShipment::where('id', $id)->with('batchLabel')->first();
        return $shipment;
    }

    /**
     * Get an index of label batches.
     *
     * @param  array $request
     * @return BatchLabel $batches
     */
    public function index($request)
    {
        $queryStrs = $this->queryStrs($request);
        $batches = BatchLabel::with('carrier', 'parcel', 'service', 'shipments')
            ->orderBy($queryStrs['sortBy'], $queryStrs['order'])
            ->paginate($queryStrs['limit']);
        return $batches;
    }

    /**
     * Updates default details of a batch.
     * One field or many fields can be passed in the request
     *
     * @param  array $request
     * @return BatchLabel $batch
     */
    public function update($request)
    {
        $batch = $this->find($request['id'], ['shipments']);
        if (!$batch) {
            return ['error' => 'Could not find label batch.'];
        }
        $fields = [
            'carrier_id',
            'service_level_id',
            'parcel_template_id',
            'weight',
            'mass_unit',
        ];
        DB::beginTransaction();
        foreach ($fields as $field) {
            $detail = array_get($request, $field);
            if ($detail !== null) {
                // update defaults for each shipment in the batch that are the same.
                $shipments = $batch->shipments()->where($field, $batch->$field)->get();
                foreach ($shipments as $shipment) {
                    $shipment->$field = $detail;
                    $shipment->save();
                }
                $batch->$field = $detail;
            }
        }
        $batch->save();
        DB::commit();
        $batch->load('shipments');
        return $batch;
    }

    /**
     * Updates details of a shipment/order.
     *
     * @param  array $request
     * @return BatchLabelShipment $shipment
     */
    public function updateShipment($request)
    {
        $shipment = BatchLabelShipment::find($request['id']);
        $fields = [
            'parcel_template_id',
            'weight',
            'mass_unit',
            'carrier_id',
            'service_level_id'
        ];
        foreach ($fields as $field) {
            $detail = array_get($request, $field);
            if ($detail !== null) {
                $shipment->$field = $detail;
            }
        }
        $shipment->save();
        return $shipment;
    }

    /**
     * Sets the defaults to the Query Strings if none is passed in.
     *
     * @param  array $request
     * @return array $queryStrs
     */
    private function queryStrs($request)
    {
        $queryStrs = [
            'sortBy' => isset($request['sortBy']) ? $request['sortBy'] : 'created_at',
            'order'  => isset($request['order'])  ? $request['order']  : 'desc',
            'limit'  => isset($request['limit'])  ? $request['limit']  : 20
        ];
        return $queryStrs;
    }
}
