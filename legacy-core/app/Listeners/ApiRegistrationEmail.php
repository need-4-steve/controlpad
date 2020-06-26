<?php

namespace App\Listeners;

use App\Mailers\ApiRegistrationMailer;
use App\Events\ApiRegistrationNew;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApiRegistrationEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ApiRegistrationMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  ApiRegistrationNew  $event
     * @return void
     */
    public function handle(ApiRegistrationNew $event)
    {
        $this->mailer->sendRegistration($event->token);
    }
}
