<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSubscriptionJob;
use App\Mail\Reminder;
use App\Jwt;
use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Services\Interfaces\V0\AuthServiceInterface;
use App\Services\V0\MessagingService;
use App\Services\V0\UserService;
use App\Services\V0\SubscriptionService;
use App\Models\Subscription;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AutoshipReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autoship:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify upcoming autoship subscriptions';

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
        $start = Carbon::now()->addDays(3)->toDateString().' 07:00:00'; // 12:00am MDT, 1:00am MST
        $end =  Carbon::now()->addDays(4)->toDateString().' 06:59:59'; // 11:59am MDT, 12:59am MST
        $tenants = $AuthService->getTenants();
        foreach ($tenants as $key => $tenant) {
            try {
                config(['database.connections.tenant.read.host' => $tenant->read_host]);
                config(['database.connections.tenant.write.host' => $tenant->write_host]);
                config(['database.connections.tenant.database' => $tenant->db_name]);
                app('db')->reconnect('tenant');
                $subscriptions = Subscription::on('tenant')
                    ->whereBetween('next_billing_at', [$start, $end])
                    ->whereNull('disabled_at')
                    ->whereNull('deleted_at')
                    ->get();
            } catch (\Exception $e) {
                continue;
            }
            foreach ($subscriptions as $subscription) {
                try {
                    SubscriptionService::calculateTotals($subscription);
                    $jwt = Jwt::create('Superadmin', $tenant);
                    $userRequest = new Request;
                    $userRequest->headers->set('Authorization', "Bearer ".$jwt);
                    $UserService = new UserService($userRequest);
                    $buyer = $UserService->getUser($subscription->buyer_pid, $subscription);
                    MessagingService::sendReminderEmail($buyer, $subscription, $tenant->org_id);
                } catch (\Exception $e) {

                }
            }
        }
    }
}
