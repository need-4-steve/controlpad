<?php

namespace App\Repositories\Eloquent;

use App\Models\ShippingInvoice;
use App\Models\ShippingRate;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use App\Services\Uniship\UnishipService;
use App\Repositories\Eloquent\CartRepository;

class UnishipRepository
{
    protected $unishipService;

    protected $cartRepository;

    public function __construct(UnishipService $unishipService, CartRepository $cartRepository)
    {
        $this->unishipService = $unishipService;
        $this->cartRepo = $cartRepository;
    }

    /**
     * Get any existing rates in the system
     *
     * @param string $invoiceKey
     */
    public function getExistingRates($invoiceKey)
    {
        return ShippingRate::where('invoice_key', $invoiceKey)
                            ->where('updated_at', '>=', Carbon::now()->subWeek())
                            ->get();
    }

    /**
     * Build a list of shipping packages to send to the api
     *
     * @param Cart $cart
     */
    public function getPackages($cart)
    {
        $product_ids = [];
        foreach ($cart->lines as $cartline) {
            $product_ids[] = $cartline->product_id;
        }
        $products = Product::with('items')->get();

        $total_weight = 0;
        $packages = [];
        foreach ($products as $product) {
            $package = [
                'weight' => 0,
                'length' => '1',
                'width' => '1',
                'height' => '1',
                'packagetype' => 'P',
                'declaredvalue' => '100'
            ];
            foreach ($product->items as $item) {
                $package['weight'] += $item->weight;
                $total_weight += $item->weight;
            }
        }

        // round up to nearest next 5 pounds
        $package['weight'] = (intval($package['weight'] / 5) + 1) * 5;

        return [
            'package' => $package,
            'totalWeight' => round($total_weight)
        ];
    }

    /**
     * Get a shipping rate and return a shipping rate
     *
     * @param array $data, SimpleXMLElement $xml_data
     */
    public function getShippingRate(
        $address,
        $service,
        $sponsor_id,
        $line_id,
        $line_type
    ) {
        // set our shipping invoice
        $shippingInvoice = new ShippingInvoice;
        // line can be either cart or order based on type
        $line = $line_type::with('lines')->find($line_id);
        // if we don't have a line and we have a cart in session use that
        if (empty($line) && !empty(session()->get('cart'))) {
            $line = session()->get('cart');
        }
        $sponsor = User::with('addresses')->find($sponsor_id);
        $shippingInvoice->user_id = $line->user_id;
        $shippingInvoice->order_id = '';
        $shippingInvoice->service = 'ALL';
        $shippingInvoice->ship_date = Carbon::now()->addWeek()->format('Y-m-d');
        $shippingInvoice->sender_state = $sponsor->addresses->first()->state;
        $shippingInvoice->sender_zip = $sponsor->addresses->first()->zip;
        $shippingInvoice->sender_country = 'US';
        $shippingInvoice->receiver_state = $address['state'];
        $shippingInvoice->receiver_zip = $address['zip'];
        $shippingInvoice->receiver_country = 'US';
        // build packages and weight
        $packages = $this->getPackages($line);
        $totalWeight = $packages['totalWeight'];
        $package = ['package' => $packages['package']];
        $shippingInvoice->packages = $package;
        // fees field needs to exist but can be blank
        $shippingInvoice->fees = [
        ];

        // call key generator to make an invoice key
        $invoiceKey = $this->makeInvoiceKey(
            $shippingInvoice->sender_zip,
            $shippingInvoice->receiver_zip,
            $totalWeight
        );

        // check if we have a current rate already
        $existingRates = $this->getExistingRates($invoiceKey);

        // if we had no existing rates
        if (count($existingRates) == 0) {
            // pull new rates
            $shippingRate = $this->unishipService->requestShipping($shippingInvoice);
            // save new rates
            $shippingRate = $this->saveShippingRates($shippingRate, $shippingInvoice, $invoiceKey);
        } else {
            // use existing rates
            $shippingRate = $existingRates;
        }

        $shippingRate->prepend(ShippingRate::where('service', 'LOCAL')->first());
        return $shippingRate;
    }

    /**
     * Make a unique invoice key so that we can reduce API calls
     *
     * @param integer $sendZip, integer $receiveZip, decimal $weight
     */
    public function makeInvoiceKey($sendZip, $receiveZip, $weight)
    {
        // generate an invoice key for tracking shipping rates
        $invoice_key = $sendZip
                        . '-'
                        . $receiveZip
                        . '-'
                        . $weight;
        return $invoice_key;
    }

    /**
     * Make a unique invoice key so that we can reduce API calls
     *
     * @param array $rates, ShippingInvoice $shippingInvoice, string $invoiceKey
     */
    public function saveShippingRates($rates, ShippingInvoice $shippingInvoice, $invoiceKey)
    {
        // delete any old existing rates
        ShippingRate::where('invoice_key', $invoiceKey)->delete();
        // save the new shipping rates
        foreach ($rates as $rate) {
            // SG is standard ground, SC is 2 day air
            if ($rate->service == 'SG' || $rate->service == 'SC') {
                $shippingRate = new ShippingRate;
                $shippingRate->invoice_key = $invoiceKey;
                $shippingRate->service = $rate->service;
                $shippingRate->ship_date = $shippingInvoice->ship_date;
                $shippingRate->weight = $shippingInvoice->packages['package']['weight'];
                $shippingRate->total = $rate->total;
                $shippingRate->dim_weight = $rate->dimweight;
                $shippingRate->zone = $rate->zone;
                if ($rate->service == 'SG') {
                    $shippingRate->commitment = 'Standard ground';
                } else {
                    $shippingRate->commitment = '2 day shipping';
                }
                $shippingRate->save();
            }
        }

        return ShippingRate::where('invoice_key', '=', $invoiceKey)->get();
    }
}
