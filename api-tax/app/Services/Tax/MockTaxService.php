<?php

namespace App\Services\Tax;

use App\Services\TaxServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Carbon\Carbon;

class MockTaxService extends TaxServiceInterface
{

    public function setup()
    {
        // Do nothing
    }

    public function getInvoice($referenceId)
    {
        return null;
    }

    public function getEstimate($taxInvoice)
    {
        $total = $this->getTotal($taxInvoice['line_items']);
        $tax = round(($total * 0.06), 2, PHP_ROUND_HALF_UP);
        return ['pid' => null, 'tax' => $tax, 'type' => $taxInvoice['type'], 'estimate' => true];
    }

    public function createInvoice($taxInvoice)
    {
        $taxInvoice['subtotal'] = $this->getTotal($taxInvoice['line_items']);
        $taxInvoice['tax'] = round(($taxInvoice['subtotal'] * 0.06), 2, PHP_ROUND_HALF_UP);
        $taxInvoice['tax_connection_id'] = $this->taxConnection->id;
        if ($taxInvoice['commit']) {
            $taxInvoice['committed_at'] = Carbon::now()->toDateTimeString();
        }
        return $this->taxInvoiceRepo->create($taxInvoice);
    }

    public function commitInvoice($taxInvoice, $orderId = null)
    {
        if (!isset($taxInvoice->committed_at) || $taxInvoice->committed_at == null) {
            $this->taxInvoiceRepo->update($taxInvoice->pid, ['committed_at' => Carbon::now()->toDateTimeString()]);
        }
    }

    public function commitList()
    {
        // Implement later
    }

    public function deleteInvoice($invoice)
    {
        return true;
    }

    public function refund($taxRefund, $originalInvoice)
    {
        // Assuming that $taxRefund has merchant_id, subtotal, type, origin_pid already
        $taxRefund['subtotal'] = -1 * abs($originalInvoice['subtotal']);
        $taxRefund['tax_connection_id'] = $this->taxConnection->id;
        $taxRefund['tax'] = round(($taxRefund['subtotal'] * 0.06), 2, PHP_ROUND_HALF_UP);

        return $this->taxInvoiceRepo->create($taxRefund);
    }

    public function getCredentialValidationArray()
    {
        return ['credentials.api_key' => 'required|string'];
    }

    public function validateCredentials()
    {
        return $this->taxConnection->credentials->api_key != 'invalidkey';
    }

    public function updateInvoice($taxInvoice)
    {
        throw new HttpException(405);
    }

    public function isAccountSame($taxConnection)
    {
        return ($taxConnection['credentials']['api_key'] != 'differentaccount');
    }

    private function getTotal($lineItems)
    {
        $total = 0.00;
        foreach ($lineItems as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    protected function getMetadata()
    {
        return [
            'service_type' => 'mock',
            'tax_connection_id' => $this->taxConnection->id
        ];
    }
}
