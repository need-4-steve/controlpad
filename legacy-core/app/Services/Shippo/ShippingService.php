<?php

namespace App\Services\Shippo;

use App\Jobs\SendShippingOrder;
use App\Repositories\Eloquent\ShipmentRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Services\Shippo\Classes\Shippo_Order;
use App\Models\Carrier;
use DB;
use Log;
use Shippo;
use Shippo_Address;
use Shippo_CarrierAccount;
use Shippo_Rate;
use Shippo_Error;
use Shippo_Shipment;
use Shippo_Transaction;

class ShippingService extends ShippoCommon
{

    public function __construct(
        OrderRepository $orderRepo,
        ShipmentRepository $shippingRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->settingService = app('globalSettings');
        $this->shipmentRepo = $shippingRepo;
    }

    /**
     * Get a specific rate to process with a shipment.
     *
     * @param string $rateId
     * @return array $rate
     */
    public function getRate($rateId)
    {
        try {
            $rate = Shippo_Rate::retrieve([
                'id' => $rateId
            ])->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }

        $rate = $this->markupRate($rate);

        return $rate;
    }

    /**
     * Get a list of rates pertaining to a shipment's to and from address
     * along with a parcel's size and weight.
     *
     * @param array $fromAddress
     * @param array $toAddress
     * @param array $parcel
     * @return array $shipment
     */
    public function getRates($fromAddress, $toAddress, $parcel)
    {
        try {
            $shipment = Shippo_Shipment::create([
                'object_purpose' => 'PURCHASE',
                'address_from' => $fromAddress,
                'address_to' => $toAddress,
                'parcels' => $parcel,
                'async' => false
            ])->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }
        if ($shipment['status'] !== 'SUCCESS' && $shipment['status'] !== 'QUEUED') {
            $shipment['error'] = $shipment['messages'][0]->__toArray();
            $shipment['httpStatus'] = 400;
            return $shipment;
        }

        $rates = [];
        foreach ($shipment['rates'] as $rate) {
            $rate = $rate->__toArray();
            $servicelevel = $rate['servicelevel'];
            $servicelevel = $servicelevel->__toArray();
            $rate['servicelevel'] = $servicelevel;
            $rate = $this->markupRate($rate);
            if (!isset($rate['duration_terms']) || $rate['duration_terms'] === '') {
                $rate['duration_terms'] = $rate['days'] . ' days.  Transit time is a non-guaranteed average. The number of days is the best estimate available.';
            }
            $rates[] = $rate;
        }
        $shipment['rates'] = $rates;
        return $shipment;
    }

    /**
     * Process shipping for a certain order so you can have
     * tracking available and be able to print labels.
     *
     * @param array $rate
     * @param string $transactionId
     * @param integer $orderId
     * @return array $shipping
     */
    public function createShipping($rate, $transactionId, $orderId = null)
    {
        try {
            $shipping = Shippo_Transaction::create([
                'rate' => $rate['object_id'],
                'label_file_type' => "PDF",
                'async' => false
            ])->__toArray();
        } catch (Shippo_Error $error) {
            return $this->errorResponse($error);
        }

        if ($shipping['status'] !== 'SUCCESS' && $shipping['status'] !== 'QUEUED') {
            $shipping['error'] = $shipping['messages'][0]->__toArray();
            $shipping['httpStatus'] = 400;
            return $shipping;
        }

        $rate = $this->markupRate($rate);

        $shipping = $this->shipmentRepo->create($shipping, $transactionId, $rate, $orderId);

        return $shipping;
    }

    /**
     * Saves Carrier's account_id depending on the shippo api key.
     *
     * @param array $rate
     * @param string $transactionId
     * @param integer $orderId
     * @return array $shipping
     */
    public function setCarriers()
    {
        $accounts['next'] = true;
        $page = 1;
        DB::table('carriers')->update(['account_id' => null]);
        while (isset($accounts['next'])) {
            try {
                $accounts = Shippo_CarrierAccount::all(['page' => $page])->__toArray();
            } catch (Shippo_Error $error) {
                return $this->errorResponse($error);
            }
            foreach ($accounts['results'] as $account) {
                $carrier = Carrier::where('token', $account->carrier)->first();
                if ($carrier) {
                    $carrier->update(['account_id' => $account->object_id]);
                }
            }
            $page++;
        }
        return Carrier::whereNotNull('account_id')->get();
    }
}
