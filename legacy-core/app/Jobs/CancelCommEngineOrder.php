<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Commission\CommissionService;
use App\Models\Order;

class CancelCommEngineOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $commissionService;
    protected $initialize;

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
        $commissionService = new CommissionService;
        $commissionService->cancelOrder($this->order);
    }
}
