<?php

namespace App\Repositories\Eloquent;

use App\Jobs\CancelOrder;
use App\Jobs\CancelInvoice;
use Carbon\Carbon;

class CancellationRepository
{
    // schedule a job to cancel orders
    public function addOrdersToCancellationQueue($orders)
    {
        foreach ($orders as $order) {
            $job = new CancelOrder($order);
            dispatch($job);
        }
    }

    // schedule a job to cancel invoices
    public function addInvoicesToCancellationQueue($invoices)
    {
        foreach ($invoices as $invoice) {
            $job = new CancelInvoice($invoice->id);
            dispatch($job);
        }
    }
}
