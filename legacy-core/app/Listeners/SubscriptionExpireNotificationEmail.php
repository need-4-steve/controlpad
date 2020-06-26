<?php

namespace App\Listeners;

use App\Events\SubscriptionExpireNotification;
use App\Mail\SubscriptionExpireNotice;
use App\Models\CustomEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SubscriptionExpireNotificationEmail
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
     * @param  SubscriptionExpireNotification  $event
     * @return void
     */
    public function handle(SubscriptionExpireNotification $event)
    {
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $notice = CustomEmail::where('title', 'expire_notice')->first();
        if ($event->subscriptionUser->user->email && filter_var($event->subscriptionUser->user->email, FILTER_VALIDATE_EMAIL)) {
            if ($notice->send_email) {
                $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
                Mail::to($event->subscriptionUser->user->email)->send(new SubscriptionExpireNotice($event->subscriptionUser, $fromEmail, $fromName, $notice));
            }
        }
    }
}
