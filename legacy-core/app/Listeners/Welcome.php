<?php

namespace App\Listeners;

use App\Events\WelcomeEvent;
use App\Mailers\WelcomeMailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Welcome
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WelcomeMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  WelcomeEvent  $event
     * @return void
     */
    public function handle(WelcomeEvent $event)
    {
        $this->mailer->sendWelcome($event->user, $event->order);
    }
}
