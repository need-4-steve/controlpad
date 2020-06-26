<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Services\Commission\CommissionService;
use DB;

class FixCommissionEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ce:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix orders and users that have an error for commission engine';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $commissionService = new CommissionService;
        $this->info("Fix Users");
        $users = User::where('comm_engine_status_id', 4)->orWhere('comm_engine_status_id', 1)->get();
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();
        foreach ($users as $user) {
            $commissionService->addUser($user, true);
            $bar->advance();
        }
        $bar->finish();
        $this->line("\n");
        $this->info("Fix Orders");
        $orders = Order::where('comm_engine_status_id', 4)->get();
        $bar = $this->output->createProgressBar(count($orders));
        $bar->start();
        foreach ($orders as $order) {
            $commissionService->addReceipt($order, true);
            $bar->advance();
        }
        $bar->finish();
        $this->line("\n");
        $this->info("Fix Completed");
    }
}
