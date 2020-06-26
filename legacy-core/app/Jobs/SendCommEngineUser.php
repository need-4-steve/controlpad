<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Commission\CommissionService;
use App\Models\User;

class SendCommEngineUser implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    protected $user;
    protected $commissionService;
    protected $initialize;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        User $user,
        bool $initialize
    ) {
        $this->user = $user;
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
            $commissionService->addUser($this->user, $this->initialize);
        } catch (Exception $e) {
            logger($e->getMessage());
        }
    }
}
