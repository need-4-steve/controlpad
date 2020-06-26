<?php

namespace App\Services\Tax;

use Exception;
use Log;
use App\Models\Cart;
use App\Jobs\CommitTaxInvoice;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use DB;

class TaxService
{

    public function __construct()
    {
        $this->apiKey = env('TAX_API_KEY');
        $this->client = new Client;
        $this->baseUrl = env('TAX_API_URL', 'https://tax.controlpadapi.com/api/v0');
    }

    public function getInvoiceForPID($pid)
    {
        return getData('/tax-invoices/' . $pid);
    }

    public function getCartEstimate(
        $billingAddress,
        $shippingAddress,
        $sendingAddress,
        $cart,
        $type,
        $storeOwnerPid
    ) {
        $requestBody = [
            'merchant_id' => $storeOwnerPid,
            'customer_id' => isset($cart->user_id) ? $cart->user_id : null,
            'billing_address' => $this->convertAddress($billingAddress),
            'to_address' => $this->convertAddress($shippingAddress),
            'from_address' => $this->convertAddress($sendingAddress),
            'line_items' => $this->convertLineItems($cart),
            'type' => $type,
            'estimate' => true
        ];

        return $this->postData('/tax-invoices', $requestBody);
    }

    public function createCartTaxInvoice(
        $billingAddress,
        $shippingAddress,
        $sendingAddress,
        $cart,
        $type,
        $storeOwnerPid,
        $commit
    ) {
        $requestBody = [
            'merchant_id' => $storeOwnerPid,
            'customer_id' => isset($cart->user_id) ? $cart->user_id : null,
            'billing_address' => $this->convertAddress($billingAddress),
            'to_address' => $this->convertAddress($shippingAddress),
            'from_address' => $this->convertAddress($sendingAddress),
            'line_items' => $this->convertLineItems($cart),
            'type' => $type,
            'commit' => $commit
        ];

        return $this->postData('/tax-invoices', $requestBody);
    }

    public function createSubscriptionTaxInvoice(
        $billingAddress,
        $shippingAddress,
        $sendingAddress,
        $subscription,
        $quantity,
        $storeOwnerPid,
        $commit
    ) {
        $requestBody = [
            'merchant_id' => $storeOwnerPid,
            'customer_id' => isset($subscription->user_id) ? $subscription->user_id : null,
            'billing_address' => $this->convertAddress($billingAddress),
            'to_address' => $this->convertAddress($shippingAddress),
            'from_address' => $this->convertAddress($sendingAddress),
            'line_items' => [
                $this->convertSubscriptionItem($subscription, $quantity)
            ],
            'type' => 'sale',
            'commit' => $commit
        ];

        return $this->postData('/tax-invoices', $requestBody);
    }

    // $receipt can either be an instance of App/Models/Order or an instance of App/Models/SubscriptionReceipt
    public function queueTaxInvoiceCommit($taxInvoicePid, $orderPid)
    {
        $job = (new CommitTaxInvoice($taxInvoicePid, $orderPid));
        dispatch($job);
    }

    public function commitTaxInvoicePid($pid, $orderId)
    {
        return $this->postData(
            '/tax-invoices/' . $pid . '/commit',
            ['order_id' => $orderId]
        );
    }

    public function refundId($pid)
    {
        return $this->postData(
            '/tax-invoices',
            [
                'origin_pid' => $pid,
                'type' => 'refund-full'
            ]
        );
    }

    private function convertAddress($address)
    {
        if (is_array($address)) {
            return [
                'name' => !empty($address['name']) ? $address['name'] : null,
                'line_1' => !empty($address['address_1']) ? $address['address_1'] : null,
                'line_2' => !empty($address['address_2']) ? $address['address_2'] : null,
                'city' => !empty($address['city']) ? $address['city'] : null,
                'state' => !empty($address['state']) ? $address['state'] : null,
                'zip' => $address['zip'],
                'country' => 'USA'
            ];
        } else {
            return [
                'name' => !empty($address->name) ? $address->name : null,
                'line_1' => !empty($address->address_1) ? $address->address_1 : null,
                'line_2' => !empty($address->address_2) ? $address->address_2 : null,
                'city' => !empty($address->city) ? $address->city : null,
                'state' => !empty($address->state) ? $address->state : null,
                'zip' => $address->zip,
                'country' => 'USA'
            ];
        }
    }

    private function convertLineItems(Cart $cart)
    {
        $lineItems = [];

        foreach ($cart->lines as $cartline) {
            $lineItems[] = [
                'subtotal' => round($cartline->quantity * $cartline->price, 2),
                'quantity' => $cartline->quantity,
                'tax_code' => $cartline->item->product->tax_class,
                'description' => $cartline->item->product->name." ".$cartline->item->size
            ];
        }

        // Bundles
        if (isset($cart->bundles)) {
            foreach ($cart->bundles as $bundle) {
                $lineItems[] = [
                    'subtotal' => round($bundle->pivot->quantity * $bundle->wholesalePrice->price, 2),
                    'quantity' => $bundle->pivot->quantity,
                    'tax_code' => $bundle->tax_class,
                    'description' => $bundle->name
                ];
            }
        }

        // Shipping
        if (isset($cart->total_shipping)) {
            $lineItems[] = [
                'subtotal' => round($cart->total_shipping, 2),
                'quantity' => 1,
                'type' => 'shipping',
                'description' => 'Shipping and Handling'
            ];
        }

        // Coupon or Discount
        if (isset($cart->coupon) || $cart->total_discount > 0) {
            $lineItems[] = [
                'subtotal' => round($cart->total_discount * -1, 2),
                'quantity' => 1,
                'type' => 'discount',
                'description' => isset($cart->coupon) ? 'Coupon ' . $cart->coupon->title : 'Custom Discount'
            ];
        }

        return $lineItems;
    }

    public function convertSubscriptionItem($subscription, $quantity)
    {
        $subtotal = isset($subscription->price->price) ?
            ($subscription->price->price * $quantity) :
            ($subscription->price * $quantity);
        return [
            'subtotal' => round($subtotal, 2, PHP_ROUND_HALF_UP),
            'quantity' => $quantity,
            'tax_code' => $subscription->tax_class,
            'description' => $subscription->title
        ];
    }

    public function commitNewOrders()
    {
        $lastOrderId = 0; // Helps paginate orders to balance db calls and memory loads
        // Only process up to existing records(max order_id). Prevents processing forever under load(overlap case)
        $maxId = DB::table('order_process')->max('order_id');
        if (empty($maxId)) {
            return;
        }
        do {
            $orderProcessList = DB::table('order_process')
                    ->select('order_id', 'tax_invoice_pid')->where('order_id', '>', $lastOrderId)
                    ->whereNull('taxes_committed')->whereNotNull('tax_invoice_pid')
                    ->where('order_id', '<=', $maxId)->limit(100)->get();
            foreach ($orderProcessList as $key => $orderProcess) {
                $lastOrderId = $orderProcess->order_id;
                try {
                    $taxInvoice = $this->commitTaxInvoicePid($orderProcess->tax_invoice_pid, $orderProcess->order_id);
                    DB::update('UPDATE order_process SET taxes_committed = ? WHERE order_id = ?', [$taxInvoice->committed_at, $orderProcess->order_id]);
                } catch (Exception $e) {
                    // Already logged, preventing skips
                }
            }
        } while ($orderProcessList !== null && $orderProcessList->isNotEmpty() && $lastOrderId < $maxId);
    }

    private function postData($path, $requestBody)
    {
        $url = $this->baseUrl . $path;
        $request = new Request(
            'POST',
            $url,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'APIkey' => $this->apiKey
            ],
            json_encode($requestBody)
        );
        try {
            return json_decode($this->client->send($request)->getBody()->getContents());
        } catch (Exception $e) {
            Log::error($e);
            return (object)['error' => 'error'];
        }
    }

    private function getData($path)
    {
        $request = new Request(
            'GET',
            $this->baseUrl . $path,
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'APIkey' => $this->apiKey
            ]
        );
        try {
            return json_decode($this->client->send($request)->getBody()->getContents());
        } catch (Exception $e) {
            Log::error($e);
            return (object)['error' => 'error'];
        }
    }
}
