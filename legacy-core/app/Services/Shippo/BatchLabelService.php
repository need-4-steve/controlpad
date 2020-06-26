<?php

namespace App\Services\Shippo;

use App\Models\BatchLabel;
use App\Models\BatchLabelShipment;
use DB;
use Log;
use Shippo;
use Shippo_Address;
use Shippo_Batch;
use Shippo_Rate;
use Shippo_Error;
use Shippo_Shipment;
use Shippo_Transaction;

class BatchLabelService extends ShippoCommon
{

    /**
     * Calculate cost of a purchased BatchLabel.
     * Iterates over each order/shipment in the batch that came back valid and will calculate the total cost of the batch.
     *
     * @param BatchLabel $batch
     * @return BatchLabel $batch
     */
    public function calculateCost($batch)
    {
        $shipments = $batch->shippo_response['batch_shipments']['results'];
        $amount = 0;
        $markup = 0;
        $totalPrice = 0;
        DB::beginTransaction();
        foreach ($shipments as $shipment) {
            if ($shipment['status'] === 'VALID') {
                Shippo::setApiKey('shippo_live_89949eda515a48c9910c273e519fd4b9e8fd0d80');
                try {
                    $shippoShipment = Shippo_Shipment::retrieve($shipment['shipment']);
                    $amount += $shippoShipment->rates_list[0]->amount;
                    $batchShipment = BatchLabelShipment::where('shippo_id', $shipment['object_id'])->with('order')->first();
                    $batchShipment->order->status = 'fulfilled';
                    $batchShipment->order->save();
                } catch (Shippo_Error $error) {
                    Log::error('Error occurred while calculating batch shipping labels. BatchLabel id => '.$batch->id);
                }
            }
        }
        $total_price = $amount + $markup;
        $successfulLabels = $batch->shippo_response['object_results']['purchase_succeeded'];
        $shippoResponse = $batch->shippo_response;
        $batch->total_price = $totalPrice;
        DB::table('batch_labels')->where('id', $batch->id)->update([
            'successful_labels' => $successfulLabels,
            'amount' => $amount,
            'markup' => $markup,
            'total_price' => $total_price
        ]);
        DB::commit();
        return $batch;
    }

    /**
     * Removes a shipment from a batch.
     *
     * @param BatchLabelShipment $shipment
     * @return BatchLabelShipment $shipment
     */
    public function removeShipment($shipment)
    {
        try {
            $shipment = Shippo_Batch::remove($shipment->batchLabel->shippo_id, [$shipment->shippo_id])->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }
        return $shipment;
    }

    /**
     * Retrieves an instance of a batch
     *
     * @param BatchLabel $batch
     * @return BatchLabel $batch
     */
    public function get($batch)
    {
        $attempts = 0;
        do {
            try {
                $shippoBatch = Shippo_Batch::retrieve($batch->shippo_id)->__toArray();
            } catch (Shippo_Error $error) {
                return $this->errorResponse($error);
            }
            if ($shippoBatch['status'] === 'VALIDATING' or $shippoBatch['status'] === 'PURCHASING') {
                sleep(2); // If batch is still 'PURCHASING' or 'VALIDATING' wait before making call again.
            }
            $attempts++;
        } while ($attempts < 10 and $shippoBatch['status'] === 'VALIDATING'
            or $attempts < 10 and $shippoBatch['status'] === 'PURCHASING'
        );

        $shippoBatch = $this->formatBatchResults($shippoBatch);
        if ($batch->status !== $shippoBatch['status']) {
            $batch->status = $shippoBatch['status'];
            if (isset($shippoBatch['label_url'][0])) {
                $batch->label_url = $shippoBatch['label_url'][0];
            }
            $batch->save();
        }
        $batch->shippo_response = $shippoBatch;
        if ($batch->status === 'PURCHASED' and is_null($batch->total_price)) {
            $batch = $this->calculateCost($batch);
        }
        $this->getShipmentStatuses($batch, $shippoBatch['batch_shipments']['results']);
        return $batch;
    }

    /**
     * Purchases a batch that has been validated.
     *
     * @param BatchLabel $batch
     * @return BatchLabel $batch
     */
    public function purchase($batch)
    {
        try {
            $shippoBatch = Shippo_Batch::purchase($batch->shippo_id)->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }
        $batch->status = $shippoBatch['status'];
        $batch = $this->get($batch, 'shipments.order');
        if ($batch->status === 'PURCHASED') {
            $this->calculateCost($batch);
        }
        return $batch;
    }

    /**
     * Validates a batch to be ready to purchase.
     *
     * @param int $batchId
     * @return BatchLabel $batch
     */
    public function validate($batchId)
    {
        $batch = BatchLabel::with(
            'shipments.order.customer',
            'shipments.order.storeOwner.businessAddress',
            'shipments.order.shippingAddress',
            'carrier',
            'service'
        )
        ->where('id', $batchId)
        ->first();
        $batchShipments = $this->batchShipments($batch);
        try {
            $shippoBatch = Shippo_Batch::create([
                'batch_shipments' => $batchShipments,
                'metadata' => json_encode(['batch_label_id' => $batch->id]),
                'object_purpose' => 'PURCHASE',
                'default_carrier_account' => $batch->carrier->account_id,
                'default_servicelevel_token' => $batch->service->token
            ])->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }
        $batch->shippo_id = $shippoBatch['object_id'];
        $batch->status = $shippoBatch['status'];
        $batch->save();
        $batch = $this->get($batch, 'shipments.order');
        return $batch;
    }

    /**
     * Formats shipments in a batch to create a batch.
     *
     * @param BatchLabel $batch
     * @return array $batchShipments
     */
    private function batchShipments($batch)
    {
        $batchShipments = [];
        foreach ($batch['shipments'] as $shipment) {
            $address_to = $this->formatAddress($shipment->order->shippingAddress, $shipment->order->customer);
            $address_from = $this->formatAddress($shipment->order->storeOwner->businessAddress, $shipment->order->storeOwner);
            $parcel = $this->getParcelDetails($shipment, $batch);
            $batchShipment = [
                'shipment' => [
                    'parcels' => $parcel,
                    'address_to' => $address_to,
                    'address_from' => $address_from,
                    'object_purpose' => 'PURCHASE'
                ],
                'metadata' => json_encode(['order_id' => $shipment->order_id, 'shipment_id' => $shipment->id]),
                'object_purpose' => 'PURCHASE'
            ];
            if (isset($shipment->carrier)) {
                $batchShipment['carrier_account'] = $shipment->carrier->account_id;
            }
            if (isset($shipment->service)) {
                $batchShipment['servicelevel_token'] = $shipment->service->token;
            }
            $batchShipments[] = $batchShipment;
        }
        return $batchShipments;
    }

    /**
     * Formats results that is sent back from Shippo.
     *
     * @param array $shippoBatch response back from Shippo
     * @return array $shippoBatch
     */
    private function formatBatchResults($shippoBatch)
    {
        $shippoBatch['batch_shipments'] = $this->toArray($shippoBatch['batch_shipments']);
        $shippoBatch['batch_shipments']['results'] = $this->getResults($shippoBatch['batch_shipments']['results']);
        $shippoBatch['object_results'] = $this->toArray($shippoBatch['object_results']);
        $shippoBatch['metadata'] = json_decode($shippoBatch['metadata'], true);
        return $shippoBatch;
    }

    /**
     * Formats messages and meta foreach shipment in a batch.
     *
     * @param array $batchResults messages and metadata for orders in a batch
     * @return array $results
     */
    private function getResults($batchResults)
    {
        $results = [];
        foreach ($batchResults as $result) {
            if (is_object($result)) {
                $result = $result->__toArray();
                $result['messages'] = $this->getMessages($result['messages']);
                $result['metadata'] = json_decode($result['metadata'], true);
                $results[] = $result;
            } else {
                $results = $result;
            }
        }
        return $results;
    }


    /**
     * Sets shipment parcel to defaults on the batch parcel if certain fields are not saved for the shipment parcel.
     *
     * @param BatchLabelShipment $shipment
     * @param BatchLabel $batch
     * @return ParcelTemplate $parcel
     */
    private function getParcelDetails($shipment, $batch)
    {
        if (isset($shipment->parcel)) {
            $parcel = $shipment->parcel;
        } else {
            $parcel = $batch->parcel;
        }
        if (isset($shipment->weight)) {
            $parcel->weight = $shipment->weight;
        } else {
            $parcel->weight = $batch->weight;
        }
        if (isset($shipment->mass_unit)) {
            $parcel->mass_unit = $shipment->mass_unit;
        } else {
            $parcel->mass_unit = $batch->mass_unit;
        }
        return $parcel;
    }

    /**
     * Save shipment statuses foreach shipment in a batch.
     *
     * @param BatchLabel $batch
     * @param array $results
     * @return array $batchShipments
     */
    private function getShipmentStatuses($batch, $results)
    {
        DB::beginTransaction();
        foreach ($results as $result) {
            $shipment = $batch->shipments->where('order_id', $result['metadata']['order_id'])->first();
            if (!isset($shipment->shippo_id)) {
                $shipment->shippo_id = $result['object_id'];
                $shipment->save();
            }
            $shipment->status = $result['status'];
            $shipment->messages = $result['messages'];
        }
        DB::commit();
    }
}
