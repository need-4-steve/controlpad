<?php

namespace App\Services\Tax;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\TaxServiceInterface;

class TaxJarTaxService extends TaxServiceInterface
{
    /** NOTICE this api version of tax jar -> amount is sometimes subtotal + shipping NOT INCLUDING TAX*/

    const SANDBOX_URL = "https://api.sandbox.taxjar.com";
    const LIVE_URL = "https://api.taxjar.com";

    const CODE_MAP = [
        'discount' => null,
        'shipping' => null,
        'exempt' => '99999'
    ];

    public function setup()
    {
        $this->client = new Client;
    }

    public function getInvoice($referenceId)
    {
        if ($referenceId == null) {
            return null;
        }

        try {
            $response = $this->client->get(
                $this->createUrl('/v2/transactions/orders/' . $referenceId),
                [
                    'headers' => $this->getHeaders()
                ]
            )->getBody()->getContents();

            return json_decode($response);
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
    }

    public function getEstimate($taxInvoice)
    {
        $totals = $this->calculateTotals($taxInvoice);
        $estimate = $this->calculateTaxes($taxInvoice, $totals);

        return [
            'pid' => null,
            'tax' => $estimate->tax->amount_to_collect,
            'type' => $taxInvoice['type'],
            'estimate' => true
        ];
    }

    public function createInvoice($taxInvoice)
    {
        $totals = $this->calculateTotals($taxInvoice);
        $estimate = $this->calculateTaxes($taxInvoice, $totals);

        if ($estimate->tax->has_nexus) {
            foreach ($estimate->tax->breakdown->line_items as $key => $line) {
                $taxInvoice['line_items'][$line->id]['tax'] = $line->tax_collectable;
            }
        }
        // Create invoice doesn't actually create any data on tax jar
        $taxInvoice['subtotal'] = $totals['subtotal'];
        $taxInvoice['shipping'] = $totals['shipping'];
        $taxInvoice['discount'] = $totals['discount'];
        $taxInvoice['tax'] = round($estimate->tax->amount_to_collect, 2);
        $taxInvoice['tax_connection_id'] = $this->taxConnection->id;
        // TODO handle auto commit parameter
        return $this->taxInvoiceRepo->create($taxInvoice, true);
    }

    private function calculateTaxes($taxInvoice, $totals)
    {
        // Calculate taxes doesn't create data in tax jar
        $body = [
            'amount' => $totals['subtotal'],
            'transaction_date' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v'),
            'shipping' => $totals['shipping']
        ];

        if (isset($taxInvoice['single_location'])) {
            $this->appendAddress($body, $taxInvoice['single_location'], 'from');
            $this->appendAddress($body, $taxInvoice['single_location'], 'to');
        } else {
            if (isset($taxInvoice['to_address'])) {
                $this->appendAddress($body, $taxInvoice['to_address'], 'to');
            }
            if (isset($taxInvoice['from_address'])) {
                $this->appendAddress($body, $taxInvoice['from_address'], 'from');
            }
        }

        // Pull lines from the original request and append them to the body
        $this->appendLines(
            $body,
            $taxInvoice['line_items'],
            ($totals['discount'] > 0.00 ? $totals['discount'] / $totals['subtotal'] : 0.00)
        );
        // Add customer id
        if (isset($taxInvoice['customer_id'])) {
            $body['customer_id'] = $taxInvoice['customer_id'];
        }

        try {
            $response = $this->client->post(
                $this->createUrl('/v2/taxes'),
                [
                    'headers' => $this->getHeaders(),
                    'json' => $body
                ]
            )->getBody()->getContents();

            return json_decode($response);
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
    }

    public function updateInvoice($taxInvoice)
    {
        throw new HttpException(405);
    }

    public function commitInvoice($taxInvoice, $orderId = null)
    {
        $taxInvoice->reference_id = ($orderId ?: $taxInvoice->pid);
        // Create transaction is the same as 'commit', original request was just an estimate
        $body = [
            'transaction_id' => $taxInvoice->reference_id,
            'transaction_date' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v'),
            'amount' => $taxInvoice->subtotal + $taxInvoice->shipping - $taxInvoice->discount,
            'shipping' => $taxInvoice->shipping,
            'sales_tax' => $taxInvoice->tax
        ];
        // Pull address from original request and append it to the body
        if (isset($taxInvoice->request['single_location'])) {
            $this->appendAddress($body, $taxInvoice->request['single_location'], 'from');
            $this->appendAddress($body, $taxInvoice->request['single_location'], 'to');
        } else {
            if (isset($taxInvoice->request['to_address'])) {
                $this->appendAddress($body, $taxInvoice->request['to_address'], 'to');
            }
            if (isset($taxInvoice->request['from_address'])) {
                $this->appendAddress($body, $taxInvoice->request['from_address'], 'from');
            }
        }
        // Pull lines from the original request and append them to the body
        $lineItems = $taxInvoice->request['line_items'];
        $this->appendLines(
            $body,
            $lineItems,
            ($taxInvoice->discount > 0.00 ? $taxInvoice->discount / $taxInvoice->subtotal : 0.00)
        );
        // Add customer id
        if (isset($taxInvoice->request['customer_id'])) {
            $body['customer_id'] = $taxInvoice->request['customer_id'];
        }

        try {
            $response = $this->client->post(
                $this->createUrl('/v2/transactions/orders'),
                [
                    'headers' => $this->getHeaders(),
                    'json' => $body
                ]
            );

            $taxInvoice['committed_at'] = Carbon::now()->toDateTimeString();
            return $taxInvoice;
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
    }

    public function commitList()
    {
        throw new HttpException(405);
    }

    public function refund($taxRefund, $originalInvoice)
    {
        $referenceId = \CPCommon\Pid\Pid::create();

        if ($taxRefund['type'] == 'refund-full') {
            // Full refund
            $body = [
                'transaction_id' => $referenceId,
                'transaction_reference_id' => $originalInvoice->reference_id,
                'transaction_date' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v'),
                'amount' => $originalInvoice->subtotal + $originalInvoice->shipping,
                'shipping' => $originalInvoice->shipping,
                'sales_tax' => $originalInvoice->tax
            ];
            // Pull address from original request and append it to the body
            $this->appendAddress($body, $originalInvoice->request['shipping_address'], 'to');

            try {
                $response = $this->client->post(
                    $this->createUrl('/v2/transactions/refunds'),
                    [
                        'headers' => $this->getHeaders(),
                        'json' => $body
                    ]
                );

                $taxRefund['subtotal'] = -1 * abs($originalInvoice['subtotal']);
                $taxRefund['tax'] = -1 * abs($originalInvoice['tax']);
                $taxRefund['reference_id'] = $referenceId;
                $taxRefund['tax_connection_id'] = $this->taxConnection->id;
                return $this->taxInvoiceRepo->create($taxRefund, true);
            } catch (RequestException $re) {
                if ($re->hasResponse()) {
                    $taxResponse = Psr7\str($re->getResponse());
                } else {
                    $taxResponse = null;
                }
                app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
                throw new HttpException(500);
            }
        } else {
            throw new HttpException(405, "Partial refunds not implemented");
        }
    }

    public function isAccountSame($taxConnection)
    {
        // Not possible to validate, just skip
        return true;
    }

    public function deleteInvoice($taxInvoice)
    {
        if ($taxInvoice->committed_at == null) {
            // Transactions only exist as a 'commit'
            return true;
        }
        if ($taxInvoice->type == 'sale') {
            $url = $this->createUrl('/v2/transactions/orders/' . $taxInvoice->reference_id);
        } elseif ($taxInvoice->type == 'refund' || $taxInvoice->type == 'refund-full') {
            $url = $this->createUrl('/v2/transactions/refunds/' . $taxInvoice->reference_id);
        }

        try {
            $response = $this->client->delete(
                $url,
                [
                    'headers' => $this->getHeaders()
                ]
            );
            return true;
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
    }

    public function validateCredentials()
    {
        try {
            $response = $this->client->get(
                $this->createUrl('/v2/categories'),
                [
                    'headers' => $this->getHeaders()
                ]
            );
            return true;
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $response = $re->getResponse();
                if ($response->getStatusCode() == 401) {
                    return false;
                }
                $taxResponse = Psr7\str($response);
            } else {
                $taxResponse = null;
            }
            app('log')->error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
    }

    private function createUrl($path)
    {
        return ($this->taxConnection->sandbox ? TaxJarTaxService::SANDBOX_URL : TaxJarTaxService::LIVE_URL) . $path;
    }

    public function getCredentialValidationArray()
    {
        return [
            'credentials.api_key' => 'required|string'
        ];
    }

    protected function getMetadata()
    {
        return [
            'service_type' => 'tax-jar',
            'tax_connection_id' => $this->taxConnection->id
        ];
    }

    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->taxConnection->credentials->api_key
        ];
    }

    private function appendAddress(&$body, $address, $prefix)
    {
        $body[$prefix . '_country'] = ((!isset($address['country']) || $address['country'] == 'USA') ? 'US' : $address['country']);
        $body[$prefix . '_zip'] = $address['zip'];
        $body[$prefix . '_state'] = $address['state'];
        if (isset($address['city'])) {
            $body[$prefix . '_city'] = $address['city'];
        }
        if (isset($address['line_1'])) {
            $body[$prefix . '_street'] = $address['line_1'] . (isset($address['line_2']) ? $address['line_2'] : '');
        }
    }

    private function appendLines(&$body, &$lines, $discountRate)
    {
        if (count($lines) == 0) {
            return;
        }
        $body['line_items'] = [];
        foreach ($lines as $key => &$lineItem) {
            if (!isset($lineItem['type'])) {
                if (!isset($lineItem['discount']) && $discountRate > 0.00) {
                    // Update discount on request object for storage
                    $lineItem['discount'] = round($lineItem['subtotal'] * $discountRate, 2);
                }
            } else {
                // Special types aren't lines in tax jar
                continue;
            }
            $body['line_items'][] = $this->convertLineItem($lineItem, $key);
        }
    }

    private function convertLineItem($item, $id)
    {
        $convert = [
            'quantity' => $item['quantity'],
            'unit_price' => round($item['subtotal'] / $item['quantity'], 2)
        ];
        if (isset($item['discount'])) {
            $convert['discount'] = $item['discount'];
        }
        if (isset($item['tax'])) {
            $convert['sales_tax'] = $item['tax'];
        }
        if (isset($item['description'])) {
            $convert['description'] = $item['description'];
        }
        if (isset($item['sku'])) {
            $convert['product_identifier'] = $item['sku'];
        }
        if (isset($item['tax_code'])) {
            $convert['product_tax_code'] = $item['tax_code'];
        }
        $convert['id'] = $id;
        return $convert;
    }

    private function calculateTotals($taxInvoice)
    {
        $subtotal = 0.00;
        $shipping = 0.00;
        $discount = 0.00;
        foreach ($taxInvoice['line_items'] as $key => $lineItem) {
            if (isset($lineItem['type'])) {
                if ($lineItem['type'] === 'discount') {
                    $discount = -$lineItem['subtotal'];
                } elseif ($lineItem['type'] === 'shipping') {
                    $shipping = $lineItem['subtotal'];
                }
            } else {
                // Calculate line item totals only
                $subtotal += $lineItem['subtotal'];
            }
        }
        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'shipping' => round($shipping, 2)
        ];
    }
}
