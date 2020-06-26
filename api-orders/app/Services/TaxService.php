<?php

namespace App\Services;

use App\Checkout;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class TaxService implements TaxServiceInterface
{
    private $taxApiUrl;

    public function __construct(Request $request)
    {
        $this->taxApiUrl = env('TAX_URL', 'https://tax.controlpadapi.com/api/v0');
    }
    public function createInvoiceForCheckout(Checkout $checkout, $businessAddress)
    {
        try {
            $requestBody = [
                'merchant_id' => $checkout->seller_pid,
                'customer_id' => $checkout->buyer_pid,
                'billing_address' => $checkout->billing_address,
                'to_address' => $checkout->shipping_address,
                'from_address' => $businessAddress,
                'line_items' => $this->buildLines($checkout),
                'type' => 'sale',
                'commit' => false
            ];
            $taxClient = new Client;
            $response = $taxClient->post(
                $this->taxApiUrl . '/tax-invoices',
                [
                    'json' => $requestBody,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            $this->logException($e);
            abort(500);
        }
    }

    public function commitTaxInvoiceForOrder($order)
    {
        try {
            $requestBody = [
                'order_pid' => $order->pid
            ];
            $taxClient = new Client;
            $response = $taxClient->post(
                $this->taxApiUrl . '/tax-invoices/' . $order->tax_invoice_pid . '/commit',
                [
                    'json' => $requestBody,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            $this->logException($e);
            abort(500);
        }
    }

    public function deleteTaxInvoice($taxInvoicePid)
    {
        try {
            $taxClient = new Client;
            $response = $taxClient->delete(
                $this->taxApiUrl . '/tax-invoices/' . $taxInvoicePid,
                [
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return true;
        } catch (RequestException $e) {
            $this->logException($e);
            return false;  // Don't throw an error because this isn't a big deal
        }
    }

    private function buildLines(Checkout $checkout)
    {
        $lines = [];
        foreach ($checkout->lines as $key => $line) {
            if (isset($line->item_id) && isset($line->items[0]->sku)) {
                $sku = $line->items[0]->sku;
            } else {
                $sku = null;
            }
            $lines[] = [
                'subtotal' => round($line->price * $line->quantity, 2),
                'quantity' => $line->quantity,
                'sku' => $sku,
                'tax_code' => (isset($line->tax_class) ? $line->tax_class : null)
            ];
        }
        if (isset($checkout->discount) && $checkout->discount > 0) {
            $lines[] = [
                'subtotal' => -$checkout->discount,
                'quantity' => 1,
                'type' => 'discount'
            ];
        }
        if (isset($checkout->shipping) && $checkout->shipping > 0) {
            $lines[] = [
                'subtotal' => $checkout->shipping,
                'quantity' => 1,
                'type' => 'shipping'
            ];
        }
        return $lines;
    }

    private function logException($re)
    {
        if ($re->hasResponse()) {
            $responseBody = Psr7\str($re->getResponse());
        } else {
            $responseBody = null;
        }
        app('log')->error(
            $re,
            [
                'request' => Psr7\str($re->getRequest()),
                'response' => $responseBody
            ]
        );
    }
}
