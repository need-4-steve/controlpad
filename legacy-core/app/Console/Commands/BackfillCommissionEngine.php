<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Services\Commission\CommissionService;
use DB;

class BackfillCommissionEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ce:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill Commission Engine';

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
        $this->info("Backfill Users");
        DB::table('users')->update(['comm_engine_status_id' => 1]);
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();
        foreach ($users as $user) {
            $commissionService->addUser($user, true);
            $bar->advance();
        }
        $bar->finish();
        $this->line("\n");
        $this->info("Backfill Orders");
        DB::table('orders')->update(['comm_engine_status_id' => 1]);
            DB::table('orderlines')->update(['in_comm_engine' => 0]);
        $orders = Order::all();
        $bar = $this->output->createProgressBar(count($orders));
        $bar->start();
        foreach ($orders as $order) {
            $commissionService->addReceipt($order, true);
            $bar->advance();
        }
        $bar->finish();
        $this->line("\n");
        $this->info("Turn Commission Engine Setting On");
        DB::table('settings')->where('key', 'use_commission_engine')->update([
            'value' => json_encode(['value' => true, 'show' => false]),
        ]);
        cache()->forget('globalSettings');
        cache()->forget('global-settings');
        DB::table('orders')->where('comm_engine_status_id', '=', 0)->update(['comm_engine_status_id' => 1]);
        $this->info("Backfill Completed");
    }
}
