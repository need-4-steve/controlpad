<?php

namespace App\Services;

use App\Checkout;

interface TaxServiceInterface
{
    public function createInvoiceForCheckout(Checkout $checkout, $businessAddress);
    public function commitTaxInvoiceForOrder($order);
    public function deleteTaxInvoice($taxInvoicePid);
}
