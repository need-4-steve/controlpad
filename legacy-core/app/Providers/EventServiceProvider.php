<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ExpiredSubscripitionRenew' => [
            'App\Listeners\ExpiredSubscripitionRenewListener',
        ],
        'App\Events\WelcomeEvent' => [
            'App\Listeners\Welcome'
        ],
        'App\Events\PasswordNewEvent' => [
            'App\Listeners\PasswordNew'
        ],
        'App\Events\OrderWasFulfilled' =>[
            'App\Listeners\EmailOrderFulfilled'
        ],
        'App\Events\SubscriptionExpireNotification' =>[
            'App\Listeners\SubscriptionExpireNotificationEmail'
        ],
        'App\Events\SubscriptionCardNotification' =>[
            'App\Listeners\SubscriptionCardNotificationEmail'
        ],
        'App\Events\SubscriptionCardUpdate' =>[
            'App\Listeners\SubscriptionCardUpdateEmail'
        ],
        'App\Events\ApiRegistrationNew' =>[
            'App\Listeners\ApiRegistrationEmail'
        ],
        'Illuminate\Mail\Events\MessageSending' => [
            'App\Listeners\EmailLoggerListener',
        ],
        'App\Events\SubscriptionCreatedEvent' => [
            'App\Listeners\SubscriptionCreatedListener'
        ],
        'App\Events\SubscriptionExpiredEvent' => [
            'App\Listeners\SubscriptionExpiredListener'
        ],
        'App\Events\SubscriptionRenewedEvent' => [
            'App\Listeners\SubscriptionRenewedListener'
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
