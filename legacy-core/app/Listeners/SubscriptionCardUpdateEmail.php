<?php

namespace App\Listeners;

use App\Events\SubscriptionCardUpdate;
use App\Mail\CardUpdate;
use App\Models\CustomEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SubscriptionCardUpdateEmail
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
     * @param  SubscriptionCardUpdate  $event
     * @return void
     */
    public function handle(SubscriptionCardUpdate $event)
    {
        $card_update = CustomEmail::where('title', 'card_update')->first();
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        if ($event->user->email && filter_var($event->user->email, FILTER_VALIDATE_EMAIL)) {
            if ($card_update->send_email) {
                $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
                Mail::to($event->user->email)->send(new CardUpdate($event->user, $fromEmail, $fromName, $card_update));
            }
        }
    }
}
