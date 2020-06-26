<?php

namespace App\Services\Tax;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Psr7\Request;
use App\Services\TaxServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repositories\Interfaces\TaxInvoiceInterface;

class ExactorTaxService extends TaxServiceInterface
{

    const BASE_URL = "https://taxrequest.exactor.com/taxrequest/v2/xml";
    const CODE_MAP = [
        'discount' => 'EUC-99010102',
        'shipping' => 'EUC-13010101',
    ];

    public function setup()
    {
        // Called by parent constructor
        $this->digitalSignature = Hash::make($this->taxConnection->credentials->merchant_id . '\n'
                            . $this->taxConnection->user_id . '\n\n\n\n\n');
        $this->client = new Client;
    }

    public function getInvoice($referenceId)
    {
        return null;
    }

    public function getEstimate($taxInvoice)
    {
        $xml = $this->postInvoice($taxInvoice, false);
        if (isset($xml->ErrorResponse)) {
            Log::error('Get estimate error.', $this->appendMetadata($xml->asXML()));
            throw new HttpException(500);
        }
        $taxAmount = (float)$xml->InvoiceResponse->TotalTaxAmount;

        $this->deleteInvoice((object)['reference_id' => (string)$xml->InvoiceResponse->TransactionId]);
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
        $taxInvoice['subtotal'] = $responseInvoice->GrossAmount;
        $taxInvoice['reference_id'] = (string)$responseInvoice->TransactionId;
        $taxInvoice['tax'] = $responseInvoice->TotalTaxAmount;
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

        $responseXml = $this->performRequest($xmlDoc);
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

            $responseXml = $this->performRequest($xmlDoc);
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
            'credentials.user_id' => 'required|string'
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
        $xml = $this->postInvoice($dummyInvoice, false);
        if (isset($xml->ErrorResponse)) {
            if (isset($xml->ErrorResponse->ErrorCode)
                && in_array($xml->ErrorResponse->ErrorCode, [2,3,4,5])) {
                // This error is for bad credentials
                return false;
            }
            Log::error('Validate credentials error.', $this->appendMetadata($xml->asXML()));
            throw new HttpException(500);
        }

        $this->deleteInvoice((object)['reference_id' => (string)$xml->InvoiceResponse->TransactionId]);
        return true;
    }

    public function isAccountSame($taxConnection)
    {
        return (
            $this->taxConnection->credentials->merchant_id ==
            $taxConnection['credentials']['merchant_id']
        );
    }

    private function getBaseDocument()
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<TaxRequest xmlns="http://www.exactor.com/ns/v2">'
            . '</TaxRequest>'
        );
        $xml->addChild('MerchantId', $this->taxConnection->credentials->merchant_id);
        $xml->addChild('UserId', $this->taxConnection->credentials->user_id);
        if (isset($this->taxConnection->credentials->partner_id)) {
            $xml->addChild('PartnerId', $this->taxConnection->credentials->partner_id);
        }
        $xml->addChild('DigitalSignature', $this->digitalSignature);
        return $xml;
    }

    private function packInvoiceRequest($xml, $taxInvoice)
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

        foreach ($taxInvoice['line_items'] as $key => $lineItem) {
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
            // Defer to specific defined tax code first because a sku might not be assigned in exactor
            $xml->addChild('SKU', $item['tax_code']);
        } elseif (isset($item['type'])) {
            $xml->addChild('SKU', ExactorTaxService::CODE_MAP[$item['type']]);
        } elseif (isset($item['sku'])) {
            $xml->addChild('SKU', $item['sku']);
        }
        $xml->addAttribute('id', '_' . (isset($item->pid) ? $item['pid'] : $id)); // Line pid, or position
    }

    private function postInvoice($taxInvoice, $commit)
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

        return $this->performRequest($xmlDoc);
    }

    public function deleteInvoice($invoice)
    {
        $xmlDoc = $this->getBaseDocument();
        $deleteRequest = $xmlDoc->addChild('DeleteRequest');
        $deleteRequest->addChild('PriorTransactionId', $invoice->reference_id);

        return $this->performRequest($xmlDoc);
    }

    private function performRequest($xml)
    {
        $request = new Request(
            'POST',
            ExactorTaxService::BASE_URL,
            ['Content-Type' => 'text/xml; charset=UTF8'],
            Stream::factory($xml->asXML())
        );

        try {
            $response = $this->client->send($request);
        } catch (ClientException $e) {
            Log::error($e, $this->getMetadata());
            throw new HttpException(500);
        }
        $responseBody = $response->getBody()->getContents();
        $response = new \SimpleXMLElement($responseBody);
        return $response;
    }

    protected function getMetadata()
    {
        return [
            'service_type' => 'exactor',
            'tax_connection_id' => $this->taxConnection->id
        ];
    }
}
