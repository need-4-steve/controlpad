<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessSubscriptionJob;
use App\Models\Subscription;
use App\Services\Interfaces\V0\AuthServiceInterface;
use Carbon\Carbon;
use DB;

class AutoshipProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoship:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process autoship subscriptions that are past their next_billing_at date';

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
        $AuthService = app()->make(AuthServiceInterface::class);
        $tenants = $AuthService->getTenants();
        foreach ($tenants as $key => $tenant) {
            try {
                config(['database.connections.tenant.read.host' => $tenant->read_host]);
                config(['database.connections.tenant.write.host' => $tenant->write_host]);
                config(['database.connections.tenant.database' => $tenant->db_name]);
                app('db')->reconnect('tenant');
                $subscriptions = Subscription::on('tenant')->select('id', 'pid')->where('next_billing_at', '<=', Carbon::now('US/Mountain')->endOfDay()->setTimezone('UTC'))->get();
                DB::transaction(function () use ($subscriptions, $tenant) {
                    foreach ($subscriptions as $subscription) {
                        $job = (new ProcessSubscriptionJob($subscription->pid, $tenant))->onQueue('autoship');
                        dispatch($job);
                    }
                });
            } catch (\Exception $e) {
            }
        }
    }
}
