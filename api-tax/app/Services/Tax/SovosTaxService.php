<?php

namespace App\Services\Tax;

use Carbon\Carbon;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Psr7\Request;
use App\Services\TaxServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repositories\Interfaces\TaxInvoiceInterface;

class SovosTaxService extends TaxServiceInterface
{

    const SANDBOX_URL = 'https://ngtd-uat.sovos.com/api/v1';
    const LIVE_URL = 'https://ngtd.sovos.com/api/v1';
    const CODE_MAP = [
        'discount' => '2048883', // TODO this might not be the correct code
        'shipping' => '2038222',
    ];

    public function setup()
    {
        // Called by parent constructor
        $this->client = new Client(['verify' => false]); // TODO change this when they get their cert stuff fixed
    }

    public function getInvoice($referenceId)
    {
        $uri = ($this->taxConnection->sandbox ? SovosTaxService::SANDBOX_URL : SovosTaxService::LIVE_URL);
        $uri = $uri . '/transactions/search?tax-id=' . $referenceId;
        $headers = $this->getHeaders();
        $request = new Request(
            'GET',
            $uri,
            $this->getHeaders()
        );

        try {
            $response = $this->client->send($request);
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            Log::error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
        $responseBody = $response->getBody()->getContents();
        $results = json_decode($responseBody);
        return $results;
    }

    private function getHeaders()
    {
        $date = Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z');
        $clientKey = $this->taxConnection->credentials->client_id;
        $merchantKey = $this->taxConnection->credentials->merchant_id;
        $headers = [];
        $headers['x-request-date'] = $date;

        $digest = base64_encode(hash_hmac(
            'sha256',
            sprintf('%s%s%s', $date, $clientKey, $merchantKey),
            $this->taxConnection->credentials->secret_key,
            true
        ));
        $authHeader = sprintf(
            '%s:%s:%s',
            $clientKey,
            $merchantKey,
            $digest
        );
        $headers['Authorization'] = $authHeader;
        $headers['Content-Type'] = 'application/xml';
        //TODO $headers['x-request-id'] =
        //TODO what is x-correlation-id ?   says Sovos request id
        return $headers;
    }

    public function getEstimate($taxInvoice)
    {
        $xml = $this->postEstimate($taxInvoice);
        if (isset($xml->ErrorResponse)) {
            Log::error('Get estimate error.', $this->appendMetadata($xml->asXML()));
            throw new HttpException(500);
        }
        $taxAmount = (float)$xml->InvoiceResponse->TotalTaxAmount;

        return ['pid' => null, 'tax' => $taxAmount, 'type' => $taxInvoice['type'], 'estimate' => true];
    }

    public function createInvoice($taxInvoice)
    {
        $xml = $this->postInvoice($taxInvoice, $taxInvoice['commit']);
        if (isset($xml->ErrorResponse)) {
            Log::error('Create invoice error.', $this->appendMetadata($xml->asXML()));
            throw new HttpException(500);
        }

        if (isset($xml->InvoiceResponse)) {
            $responseInvoice = $xml->InvoiceResponse;
            $committed_at = null;
        } elseif (isset($xml->CommitResponse) && isset($xml->CommitResponse->InvoiceResponse)) {
            $committed_at = Carbon::now()->toDateTimeString();
            $responseInvoice = $xml->CommitResponse->InvoiceResponse;
        } else {
            Log::error('Create invoice error.', $this->appendMetadata($xml->asXML()));
        }
        $taxInvoice['reference_id'] = (string)$responseInvoice->TransactionId;
        $taxInvoice['tax'] = round((double)$responseInvoice->TotalTaxAmount, 2);
        $taxInvoice['tax_connection_id'] = $this->taxConnection->id;
        $taxInvoice['committed_at'] = $committed_at;

        return $this->taxInvoiceRepo->create($taxInvoice);
    }

    public function updateInvoice($taxInvoice)
    {
        throw new HttpException(405);
    }

    public function commitInvoice($taxInvoice, $orderId = null)
    {
        $xmlDoc = $this->getBaseDocument();
        $commitRequest = $xmlDoc->addChild('CommitRequest');
        $commitRequest->addChild('CommitDate', Carbon::now()->format('Y-m-d'));
        $commitRequest->addChild('PriorTransactionId', $taxInvoice['reference_id']);
        if ($orderId !== null) {
            $commitRequest->addChild('InvoiceNumber', $orderId);
        }
        if (isset($taxInvoice['order_pid'])) {
            $commitRequest->addChild('PurchaseOrderNumber', $taxInvoice['order_pid']);
        }

        $responseXml = $this->performRequest($xmlDoc, '/transactions/commit');
        if (isset($responseXml->ErrorResponse)) {
            Log::error('Commit invoice error.', $this->appendMetadata($responseXml->asXML(), $taxInvoice->pid));
            throw new HttpException(500);
        }
        $taxInvoice['committed_at'] = Carbon::now()->toDateTimeString();
        return $taxInvoice;
    }

    public function commitList()
    {
        throw new HttpException(405);
    }

    public function refund($taxRefund, $originalInvoice)
    {
        if ($taxRefund['type'] == 'refund-full') {
            // Full refund
            $xmlDoc = $this->getBaseDocument();
            $refundRequest = $xmlDoc->addChild('RefundRequest');
            $refundRequest->addChild('RefundDate', Carbon::now()->format('Y-m-d'));
            $refundRequest->addChild('PriorTransactionId', $originalInvoice['reference_id']);

            $responseXml = $this->performRequest($xmlDoc, '/transactions/refund');
            if (isset($responseXml->ErrorResponse)) {
                Log::error('Refund invoice error.', $this->appendMetadata($responseXml->asXML(), $originalInvoice->pid));
                throw new HttpException(500);
            }
            $taxRefund['subtotal'] = -1 * abs($originalInvoice['subtotal']);
            $taxRefund['tax'] = -1 * abs($originalInvoice['tax']);
            $taxRefund['reference_id'] = (string)$responseXml->RefundResponse->TransactionId;
            $taxRefund['tax_connection_id'] = $this->taxConnection->id;
            return $this->taxInvoiceRepo->create($taxRefund);
        } else {
            throw new HttpException(405, "Partial refunds not implemented");
        }
    }

    public function getCredentialValidationArray()
    {
        return [
            'credentials.merchant_id' => 'required|string',
            'credentials.client_id' => 'required|string',
            'credentials.secret_key' => 'required|string',
            // 'credentials.partner_id' => 'required|string'
        ];
    }

    public function validateCredentials()
    {
        // Test posting and deleting a zero dollar invoice
        $dummyInvoice = [
            'type' => 'sale',
            'single_location' => [
                'state' => 'UT',
                'zip' => '84604'
            ],
            'line_items' => [
                [
                    'subtotal' => 0.00,
                    'quantity' => 1
                ]
            ]
        ];
        $xml = $this->postEstimate($dummyInvoice);
        if (isset($xml->ErrorResponse)) {
            if (isset($xml->ErrorResponse->ErrorCode)
                && in_array($xml->ErrorResponse->ErrorCode, [2,3,4,5])) {
                // This error is for bad credentials
                return false;
            }
            Log::error('Validate credentials error.', $this->appendMetadata($xml->asXML()));
            throw new HttpException(500);
        }

        return true;
    }

    public function isAccountSame($taxConnection)
    {
        return (
            $this->taxConnection->credentials->client_id ==
            $taxConnection['credentials']['client_id'] &&
            $this->taxConnection->credentials->merchant_id ==
            $taxConnection['credentials']['merchant_id']
        );
    }

    private function getBaseDocument()
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<TaxRequest>'
            . '</TaxRequest>'
        );
        // $xml->addChild('PartnerId', $this->taxConnection->credentials->partner_id);
        return $xml;
    }

    private function packInvoiceRequest($xml, &$taxInvoice)
    {
        $invoiceXml = $xml->addChild('InvoiceRequest');
        $invoiceXml->addChild('SaleDate', Carbon::now()->format('Y-m-d'));
        $invoiceXml->addChild('CurrencyCode', 'USD');
        if ($taxInvoice['type'] == 'use') {
            $invoiceXml->addChild('TaxClass', 'Use');
        }
        if (isset($taxInvoice['customer_id'])) {
            $invoiceXml->addChild('ExemptionId', $taxInvoice['customer_id']);
        }
        $this->packAddresses($invoiceXml, $taxInvoice);
        $subtotal = 0.00;
        $shipping = 0.00;
        $discount = 0.00;
        $discountRate = 1;
        $discountKey = null;
        foreach ($taxInvoice['line_items'] as $key => $lineItem) {
            if (isset($lineItem['type'])) {
                if ($lineItem['type'] === 'discount') {
                    $discountKey = $key;
                    $discount = $lineItem['subtotal'];
                } elseif ($lineItem['type'] === 'shipping') {
                    $shipping = $lineItem['subtotal'];
                }
            } else {
                // Calculate line item totals only
                $subtotal += $lineItem['subtotal'];
            }
        }
        if ($discountKey) {
            unset($taxInvoice['line_items'][$discountKey]);
            $discountRate = 1 + $discount / $subtotal;
        }
        $taxInvoice['subtotal'] = round($subtotal + $shipping + $discount, 2);
        foreach ($taxInvoice['line_items'] as $key => $lineItem) {
            if (!isset($lineItem['type'])) {
                // Refactor items for any available discount, instead of discount being a line item
                $lineItem['subtotal'] = round($lineItem['subtotal'] * $discountRate, 2);
            } elseif ($lineItem['type'] == 'shipping') {
                // Special case for moving shipping line to top of document
                $invoiceXml->addChild('DeliveryAmount', $shipping);
                continue;
            }
            $this->packLineItem($invoiceXml->addChild('LineItem'), $lineItem, $key);
        }
    }

    /**
    * $xml - xml element to put addresses into (such as invoice request object)
    * $params - request array of address for $body level
    */
    private function packAddresses($xml, $params)
    {
        if (isset($params['billing_address'])) {
            $this->packAddress($xml->addChild('BillTo'), $params['billing_address']);
        }
        if (isset($params['single_location'])) {
            $this->packAddress($xml->addChild('ShipTo'), $params['single_location']);
            $this->packAddress($xml->addChild('ShipFrom'), $params['single_location']);
        } else {
            if (isset($params['to_address'])) {
                $this->packAddress($xml->addChild('ShipTo'), $params['to_address']);
            }
            if (isset($params['from_address'])) {
                $this->packAddress($xml->addChild('ShipFrom'), $params['from_address']);
            }
        }
    }

    /**
    * Converts request address format and insert into xml address element
    */
    private function packAddress($xml, $address)
    {
        if (isset($address['name'])) {
            $xml->addChild('FullName', htmlspecialchars($address['name']));
        }
        if (isset($address['line_1'])) {
            $xml->addChild('Street1', htmlspecialchars($address['line_1']));
        }
        if (isset($address['line_2'])) {
            $xml->addChild('Street2', htmlspecialchars($address['line_2']));
        }
        if (isset($address['city'])) {
            $xml->addChild('City', htmlspecialchars($address['city']));
        }
        if (isset($address['state'])) {
            $xml->addChild('StateOrProvince', htmlspecialchars($address['state']));
        }
        $xml->addChild('PostalCode', htmlspecialchars($address['zip']));
        $xml->addChild(
            'Country',
            (isset($address['country']) ? htmlspecialchars($address['country']) : 'USA')
        );
    }

    private function packLineItem($xml, $item, $id)
    {
        if (isset($item['description'])) {
            $xml->addChild('Description', htmlspecialchars($item['description']));
        }
        $xml->addChild('Quantity', $item['quantity']);
        $xml->addChild('GrossAmount', $item['subtotal']);
        if (isset($item['tax_code'])) {
            // Defer to specific defined tax code first because a sku might not be assigned in sovos
            $xml->addChild('SKU', $item['tax_code']);
        } elseif (isset($item['type'])) {
            $xml->addChild('SKU', SovosTaxService::CODE_MAP[$item['type']]);
        } elseif (isset($item['sku'])) {
            $xml->addChild('SKU', $item['sku']);
        }
        $xml->addAttribute('id', '_' . (isset($item->pid) ? $item['pid'] : $id)); // Line pid, or position
    }

    private function postEstimate(&$taxInvoice)
    {
        $xmlDoc = $this->getBaseDocument();
        $this->packInvoiceRequest($xmlDoc, $taxInvoice);

        return $this->performRequest($xmlDoc, '/transactions/evaluate');
    }

    private function postInvoice(&$taxInvoice, $commit)
    {
        $xmlDoc = $this->getBaseDocument();
        if ($commit) {
            // Pack a CommitRequest
            $commitRequest = $xmlDoc->addChild('CommitRequest');
            $commitRequest->addChild('CommitDate', Carbon::now()->format('Y-m-d'));
            $this->packInvoiceRequest($commitRequest, $taxInvoice);
        } else {
            // Pack an InvoiceRequest
            $this->packInvoiceRequest($xmlDoc, $taxInvoice);
        }

        return $this->performRequest($xmlDoc, '/transactions/calculate');
    }

    public function deleteInvoice($invoice)
    {
        $xmlDoc = $this->getBaseDocument();
        $deleteRequest = $xmlDoc->addChild('DeleteRequest');
        $deleteRequest->addChild('PriorTransactionId', $invoice->reference_id);

        return $this->performRequest($xmlDoc, '/transactions', 'DELETE');
    }

    private function performRequest($xml, $path, $method = 'POST')
    {
        $request = new Request(
            $method,
            ($this->taxConnection->sandbox ? SovosTaxService::SANDBOX_URL : SovosTaxService::LIVE_URL) . $path,
            $this->getHeaders(),
            Stream::factory($xml->asXML())
        );

        try {
            $response = $this->client->send($request);
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                $taxResponse = Psr7\str($re->getResponse());
            } else {
                $taxResponse = null;
            }
            Log::error($re, $this->appendMetadata($taxResponse, null, Psr7\str($re->getRequest())));
            throw new HttpException(500);
        }
        $responseBody = $response->getBody()->getContents();
        $response = new \SimpleXMLElement($responseBody);
        return $response;
    }

    protected function getMetadata()
    {
        return [
            'service_type' => 'sovos',
            'tax_connection_id' => $this->taxConnection->id
        ];
    }
}
