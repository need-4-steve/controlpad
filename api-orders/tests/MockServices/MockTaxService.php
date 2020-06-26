<?php

namespace Test\MockServices;

use App\Checkout;
use Carbon\Carbon;
use CPCommon\Pid\Pid;

class MockTaxService implements \App\Services\TaxServiceInterface
{
    public function createInvoiceForCheckout(Checkout $checkout, $businessAddress)
    {
        return (object) [
            'pid' => Pid::create(),
            'amount' => round(($checkout->subtotal - $checkout->discount) * 0.06, 2),
            'committed_at' => null
        ];
    }

    public function function commitTaxInvoiceForOrder($order)
    {
        return (object) [
            'pid' => $order->$tax_invoice_pid,
            'committed_at' => Carbon::now()->setTimezone('UTC')->toDateTimeString()
        ];
    }

    public function deleteTaxInvoice($taxInvoicePid)
    {
        return true;
    }
}
