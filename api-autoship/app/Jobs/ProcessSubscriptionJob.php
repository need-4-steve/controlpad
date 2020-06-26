<?php

namespace App\Jobs;

use App\Models\Attempt;
use App\Models\Subscription;
use App\Jwt;
use App\Repositories\Eloquent\V0\SubscriptionRepository;
use App\Services\V0\OrderService;
use App\Services\V0\UserService;
use App\Services\V0\SubscriptionService;
use CPCommon\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProcessSubscriptionJob extends Job
{
    protected $subscriptionPid;
    protected $tenant;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subscriptionPid, $tenant)
    {
        $this->subscriptionPid = $subscriptionPid;
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Needs to have a separate connection to be able to delete the job on the initial connection once the job is done.
        config(['database.connections.tenant.read.host' => $this->tenant->read_host]);
        config(['database.connections.tenant.write.host' => $this->tenant->write_host]);
        config(['database.connections.tenant.database' => $this->tenant->db_name]);
        app('db')->reconnect('tenant');
        $SubscriptionRepo = new SubscriptionRepository('tenant');

        // Find expired subscriptions
        $subscription = $SubscriptionRepo->find(['expands' => ['lines', 'cycle_attempts']], $this->subscriptionPid);
        if (!isset($subscription)) {
            app('log')->error('Autoship Subscription Not Found', [
                'fingerprint' => 'Autoship Subscription Not Found',
                'subscription_pid' => $this->subscriptionPid,
                'org_id' => $this->tenant['org_id'],
            ]);
            abort(500, 'Autoship Subscription Not Found');
        }
        // This is to prevent an autoship subscription from being billed twice in a row
        if ($subscription->next_billing_at > Carbon::now()) {
            abort(500, 'Subscription cannot be processed until after '.$subscription->next_billing_at);
        }

        // Create a generic jwt to get the buyer
        $jwt = Jwt::create('Superadmin', $this->tenant);
        $userRequest = new Request;
        $userRequest->headers->set('Authorization', "Bearer ".$jwt);
        $UserService = new UserService($userRequest);
        $buyer = $UserService->getBuyer($subscription->buyer_pid, $subscription);

        // Create a jwt from the specific buyer to checkout
        $buyerJwt = Jwt::create('Superadmin', $this->tenant, $buyer);
        $orderRequest = new Request;
        $orderRequest->headers->set('Authorization', "Bearer ".$buyerJwt);
        $OrderService = new OrderService($orderRequest);
        $checkout = $OrderService->createCheckout($subscription, $buyer);
        $order = $OrderService->checkout($subscription, $buyer, $checkout);
        SubscriptionService::createAttempt($subscription, 'success', 'success', (isset($order->order->pid) ? $order->order->pid : null));
        $order->subscription = $subscription;
        return response()->json($order, 200);
    }
}
