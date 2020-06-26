<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Order;
use App\Services\Shippo\ShippingServiceExport;

class SendShippingOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        Order $order
    ) {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shippingServiceExport = new ShippingServiceExport;
        $shippingServiceExport->exportOrder($this->order);
    }
}
