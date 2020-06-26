<?php

namespace App\Services;

use App\Repositories\Interfaces\TaxInvoiceInterface;

abstract class TaxServiceInterface
{

    protected $taxInvoiceRepo;
    protected $taxConnection;

    public function __construct($taxConnection, TaxInvoiceInterface $taxInvoiceInterface = null)
    {
        $this->taxConnection = $taxConnection;
        $this->taxInvoiceRepo = $taxInvoiceInterface;
        $this->setup();
    }

    protected function appendMetadata($taxResponse = null, $taxInvoicePid = null, $taxRequest = null)
    {
        return array_merge(
            [
                'tax_response' => $taxResponse,
                'tax_invoice_pid' => $taxInvoicePid,
                'tax_request' => $taxRequest
            ],
            $this->getMetadata()
        );
    }

    abstract protected function getMetadata();
    abstract public function setup();
    abstract public function getInvoice($referenceId);
    abstract public function getEstimate($taxInvoice);
    abstract public function createInvoice($taxInvoice);
    abstract public function updateInvoice($taxInvoice);
    abstract public function commitInvoice($taxInvoice, $orderId = null);
    abstract public function commitList();
    abstract public function refund($taxRefund, $originalInvoice);
    abstract public function getCredentialValidationArray();
    abstract public function validateCredentials();
    abstract public function isAccountSame($taxConnection);
    abstract public function deleteInvoice($invoice);
}
