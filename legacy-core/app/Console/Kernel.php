<?php

namespace App\Console;

use App\Models\CardToken;
use App\Models\SubscriptionUser;
use App\Models\Inventory;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Cache;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MkStorage::class,
        Commands\ProcessQueueAndExit::class,
        Commands\PackageList::class,
        Commands\GitResetPull::class,
        Commands\BackfillCommissionEngine::class,
        Commands\FixCommissionEngine::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        try {
            /* Kill cached MComm Period value */
            $schedule->call(function () {
                $periodCache=cache('mcomm_period');
                if ($periodCache=cache('mcomm_period')) {
                    cache()->forget('mcomm_period');
                }
            })->dailyAt('23:59');

            /* Change rep ownership of Fulfilled by Corporate inventory,
                that has been sold out and expired, back to corporate. */
            $schedule->call('App\Repositories\Eloquent\FulfilledByCorporateRepository@changeOwnerOfSoldOut')->dailyAt('07:00');

            // Fix inventory with owner_id of 0
            $schedule->call('App\Repositories\Eloquent\FulfilledByCorporateRepository@fixInventoryOwnerIdOfZero')->dailyAt('07:15');

            // delete out carts that are to old
            $schedule->call(function () {
                DB::table('carts')
                    ->where('updated_at', '<=', Carbon::now()->subHours(2))
                    ->delete();
            })->hourly();  // run the task hourly

            // renew expired subscriptions
            $schedule->call('App\Services\Subscription\SubscriptionService@renewExpiredSubscriptions')->dailyAt('08:00');

            // renew expired free subscriptions
            $schedule->call('App\Services\Subscription\SubscriptionService@renewExpiredFreeSubscriptions')->dailyAt('08:00');

            // notification card not on file
            $schedule->call('App\Services\Subscription\SubscriptionService@subscriptionsWithOutCards')->weekly()->mondays()->at('8:00');

            // notification subscription is comming due
            $schedule->call('App\Services\Subscription\SubscriptionService@subscriptionsNotification')->dailyAt('8:00');

            // notification of having a bad card on files
            $schedule->call('App\Services\Subscription\SubscriptionService@subscriptionsBadCard')->weekly()->mondays()->at('8:00');

            // Find subs that are not going to auto renew and mark them not active
            $schedule->call('App\Services\Subscription\SubscriptionService@disableExpiredSubscriptions')->dailyAt('09:00');

            // remove email logs over 90 days
            $schedule->call('App\Http\Controllers\Api\V1\EmailMessageController@removeOldlogs')->dailyAt('8:30');

            // removing invoices that have expired.
            $schedule->call('App\Repositories\Eloquent\InvoiceRepository@removeExpiredInvoices')->hourly();

            // to backfill any missing transation ids
            $schedule->call('App\Repositories\Eloquent\OrderRepository@backfillTransationId')->dailyAt('08:00');

            // Fill new orders into order_process table, not in background so the order processes wait until list is updated
            $orgId = env('ORG_ID', 'null');
            $schedule->call('App\Services\Orders\OrderService@addNewOrdersToProcessTable')->name($orgId.':addNewOrdersToProcessTable')->withoutOverlapping(60)->everyMinute();
            // Emails in production
            $schedule->call('App\Services\Email\EmailService@processNewOrders')->name($orgId.':emailNewOrders')->withoutOverlapping(60)->everyMinute()->runInBackground();
            // Commit taxes for new orders
            $schedule->call('App\Services\Tax\TaxService@commitNewOrders')->name($orgId.':commitTaxNewOrders')->withoutOverlapping(60)->everyMinute()->runInBackground();
            // Send emails for new invoices
            $schedule->call('App\Services\Invoices\InvoiceService@sendNewEmails')->name($orgId.':emailNewInvoices')->withoutOverlapping(60)->everyMinute()->runInBackground();
            // Commit new orders to commissions
            if (empty(env('COMMISSION_ENGINE'))||strtolower(env('COMMISSION_ENGINE'))!=='mcom'){
                $schedule->call('App\Services\Commission\CommissionService@commitNewOrders')->name($orgId.':CommEngineNewOrders')->withoutOverlapping(60)->everyMinute()->runInBackground();
            }else{
                $schedule->call('App\Services\Commission\MCommCommissionService@commitNewOrders')->name($orgId.':CommEngineNewOrders')->withoutOverlapping(5)->everyFiveMinutes()->runInBackground();
                $schedule->call('App\Http\Controllers\Api\V2\MCommController@getCurrentPeriod')->name($orgId.':CommEngineCheckPeriod')->dailyAt('00:00');
            }
            
            // backfill missing orders in commission engine
            $schedule->call('App\Http\Controllers\Api\V1\CommissionEngineController@backfillErrorUsers')->dailyAt('08:45');

            // backfill missing orders in commission engine
            $schedule->call('App\Http\Controllers\Api\V1\CommissionEngineController@backfillErrorOrders')->dailyAt('09:00');

            // backfill cancelled orders in commission engine
            $schedule->call('App\Http\Controllers\Api\V1\CommissionEngineController@backfillCancelledErroredOrders')->dailyAt('09:15');

            // catch reps that have a missing subscription
            $schedule->call('App\Services\Registration\RegistrationService@fixRepsWithoutSubscription')->dailyAt('06:00');

            //remove temp files
            $schedule->call(function () {
                Storage::disk('public')->deleteDirectory('orders');
            })->daily();

            $schedule->call('App\Services\Subscription\AutoshipService@sendPaymentReminders')->name($orgId.':sendPaymentReminders')->withoutOverlapping(60)->dailyAt('14:00');

        } catch (Exception $e) {
            Log::error('Error in scheduler ' . $e->getMessage());
        }
    }
}
