<?php

namespace App\Listeners;

use App\Events\PasswordNewEvent;
use App\Mailers\PasswordNewMailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordNew
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PasswordNewMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  PasswordNewEvent  $event
     * @return void
     */
    public function handle(PasswordNewEvent $event)
    {
        $user = $event->user;
        $this->mailer->sendPasswordNew($user);
    }
}
