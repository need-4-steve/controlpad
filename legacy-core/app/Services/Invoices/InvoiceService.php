<?php

namespace App\Services\Invoices;

use DB;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Mailers\InvoiceNewMailer;
use Carbon\Carbon;

class InvoiceService
{
    public function sendNewEmails()
    {
        $mailer = new InvoiceNewMailer;
        
        $maxId = Invoice::max('id');
        if (empty($maxId)) {
            return;
        }
        $minDate = Carbon::now()->subDay()->toDateTimeString();
        $lastId = 0;
        do {
            $invoices = Invoice::where('id', '>', $lastId)->where('id', '<=', $maxId)
                        ->whereNull('emails_sent')
                        ->where('created_at', '>', $minDate)
                        ->limit(50)->get();
            foreach ($invoices as $invoice) {
                $lastId = $invoice->id;
                try {
                    $mailer->sendInvoice($invoice);
                    DB::statement('UPDATE invoices SET emails_sent = NOW() WHERE id = ?', [$invoice->id]);
                } catch (\Exception $e) {
                    app('log')->error($e);
                }
            }
        } while ($invoices !== null && $invoices->isNotEmpty() && $lastId < $maxId);
    }
}
