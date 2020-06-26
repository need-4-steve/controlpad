<?php

namespace App\Listeners;

use App\Events\SubscriptionCardNotification;
use App\Mail\SubscriptionToken;
use App\Models\CustomEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SubscriptionCardNotificationEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->settings = app('globalSettings');
    }

    /**
     * Handle the event.
     *
     * @param  SubscriptionCardNotification  $event
     * @return void
     */
    public function handle(SubscriptionCardNotification $event)
    {
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $missing_card = CustomEmail::where('title', 'missing_card')->first();
        if ($event->subscription->user->email && filter_var($event->subscription->user->email, FILTER_VALIDATE_EMAIL)) {
            if ($missing_card->send_email) {
                $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
                Mail::to($event->subscription->user->email)->send(new SubscriptionToken($event->subscription->user, $fromEmail, $fromName, $missing_card));
            }
        }
    }
}
