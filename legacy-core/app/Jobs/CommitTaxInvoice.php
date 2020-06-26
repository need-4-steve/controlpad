<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Order;
use App\Services\Tax\TaxService;

class CommitTaxInvoice implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $taxInvoicePid;
    protected $orderPid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $taxInvoicePid,
        $orderPid
    ) {
        $this->taxInvoicePid = $taxInvoicePid;
        $this->orderPid = $orderPid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $taxInvoice = (new TaxService)->commitTaxInvoicePid($this->taxInvoicePid, $this->orderPid);
            if (isset($taxInvoice->error)) {
                logger()->error(['TaxInvoice for '.$this->orderPid.' not commited', json_encode($taxInvoice)]);
            }
        } catch (\Exception $e) {
            logger()->error(['TaxInvoice for '.$this->orderPid.' not commited', $e->getMessage()]);
        }
    }
}
