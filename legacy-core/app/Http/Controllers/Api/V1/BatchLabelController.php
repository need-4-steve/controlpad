<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BatchLabelUpdateRequest;
use App\Http\Requests\BatchShipmentUpdateRequest;
use App\Models\BatchLabel;
use App\Repositories\Eloquent\BatchLabelRepository;
use App\Services\Shippo\BatchLabelService;
use Validator;

class BatchLabelController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  BatchLabelRepository $batchLabelRepo
     * @param  BatchLabelService $batchLabelService
     * @return void
     */
    public function __construct(
        BatchLabelRepository $batchLabelRepo,
        BatchLabelService $batchLabelService
    ) {
        $this->batchLabelRepo = $batchLabelRepo;
        $this->batchLabelService = $batchLabelService;
    }

    /**
     * Deletes a batch that has not been payed for.
     *
     * @param  int $id
     * @return Response
     */
    public function delete($id)
    {
        $batch = $this->batchLabelRepo->delete($id);
        if (isset($batch['error'])) {
            return response()->json($batch['error'], HTTP_BAD_REQUEST);
        }
        return response()->json('Batch deleted', HTTP_SUCCESS);
    }

    /**
     * Deletes a shipment/order within a batch.
     *
     * @param  int $id This is the id of the shipment.
     * @return Response
     */
    public function deleteShipment($id)
    {
        $shipment = $this->batchLabelRepo->findShipment($id);
        $service = $this->batchLabelService->removeShipment($shipment);
        if (isset($service['error'])) {
            return response()->json($service['error'], $service['httpStatus']);
        }
        $this->batchLabelRepo->deleteShipment($id);
        return response()->json(['Shipment deleted'], HTTP_SUCCESS);
    }

    /**
     * Get an index of label batches.
     *
     * @return Response
     */
    public function index()
    {
        $request = request()->all();
        $batches = $this->batchLabelRepo->index($request);
        return response()->json($batches, HTTP_SUCCESS);
    }

    /**
     * Creates a new instance of a batch.
     * The request sends a list of receipt_id of each order in the batch.
     *
     * @return Response
     */
    public function create()
    {
        $request = request()->all();
        if (count($request) > 100) {
            return response()->json(['Batches can only have up to 100 orders in it.'], HTTP_UNPROCESSABLE);
        }
        $batch = $this->batchLabelRepo->create($request);
        return response()->json($batch, HTTP_SUCCESS);
    }

    /**
     * Purchases the batch if it is valid from the service.
     *
     * @return Response
     */
    public function purchase()
    {
        $request = request()->all();
        $batch = $this->batchLabelRepo->find($request, 'shipments.order');
        unset($batch->shippo_response);

        if (count($batch->shipments) === 0) {
            return response()->json(['Batch must have shipments to purchase.'], HTTP_UNPROCESSABLE);
        }
        if (!$batch) {
            return response()->json(['Could not find label batch.'], HTTP_BAD_REQUEST);
        }
        if ($batch->status !== 'VALID') {
            return response()->json(['Only batches in VALID status can be purchased.'], HTTP_BAD_REQUEST);
        }
        $batch = $this->batchLabelService->purchase($batch);
        if (isset($batch['error'])) {
            return response()->json($batch['error'], $batch['httpStatus']);
        }
        return response()->json($batch, HTTP_SUCCESS);
    }

    /**
     * Retrieves data for a specific instance of a batch.
     *
     * @param  int $id This is the id of the shipment.
     * @return Response
     */
    public function show($id)
    {
        $batch = $this->batchLabelRepo->find($id, 'shipments.order');
        if (isset($batch['error'])) {
            return response()->json($batch['error'], $batch['httpStatus']);
        }
        if (!$batch) {
            return response()->json(['Could not find label batch.'], HTTP_BAD_REQUEST);
        }
        return response()->json($batch, HTTP_SUCCESS);
    }

    /**
     * Updates default details of a batch.
     * One field or many fields can be passed in the request
     *
     * @param  BatchLabelUpdateRequest $batchRequest
     * @return Response
     */
    public function update(BatchLabelUpdateRequest $batchRequest)
    {
        $request = $batchRequest->all();
        $batch = $this->batchLabelRepo->update($request);
        if (isset($batch['error'])) {
            return response()->json($batch['error'], HTTP_BAD_REQUEST);
        }
        return response()->json($batch, HTTP_SUCCESS);
    }


    /**
     * Updates details of a shipment/order.
     *
     * @param  BatchShipmentUpdateRequest $shipmentUpdateRequest
     * @return Response
     */
    public function updateShipment(BatchShipmentUpdateRequest $shipmentUpdateRequest)
    {
        $request = $shipmentUpdateRequest->all();
        $shipment = $this->batchLabelRepo->updateShipment($request);
        if (isset($shipment['error'])) {
            return response()->json($batch['error'], $batch['httpStatus']);
        }
        return response()->json($shipment, HTTP_SUCCESS);
    }

    /**
     * Sends the batch to the service to be validated before purchase.
     *
     * @return Response
     */
    public function validateBatch()
    {
        $request = request()->all();
        $batch = $this->batchLabelRepo->find($request, 'shipments.order');
        if (isset($batch['error'])) {
            return response()->json($batch['error'], $batch['httpStatus']);
        }
        if (!$batch) {
            return response()->json(['Could not find label batch.'], HTTP_BAD_REQUEST);
        }
        if (!is_null($batch->status)) {
            return response()->json(['Batch has already been validated.'], HTTP_BAD_REQUEST);
        }
        $validator = Validator::make($batch->toArray(), BatchLabel::$rules);

        if ($validator->fails()) {
            return response()->json($validator->messages(), HTTP_BAD_REQUEST);
        }
        $batch = $this->batchLabelService->validate($request);
        if (isset($batch['error'])) {
            return response()->json($batch['error'], $batch['httpStatus']);
        }
        return response()->json($batch, HTTP_SUCCESS);
    }
}
