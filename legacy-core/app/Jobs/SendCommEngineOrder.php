<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Commission\CommissionService;
use App\Models\Order;

class SendCommEngineOrder implements ShouldQueue
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
        Order $order,
        bool $initialize
    ) {
        $this->order = $order;
        $this->initialize = $initialize;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $commissionService = new CommissionService;
            $commissionService->addReceipt($this->order, $this->initialize);
        } catch (Exception $e) {
            logger($e->getMessage());
        }
    }
}
