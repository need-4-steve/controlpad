<?php

namespace App\Listeners;

use App\Events\OrderWasFulfilled;
use App\Mailers\OrderFulfilledMailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailOrderFulfilled implements ShouldQueue
{
    use InteractsWithQueue;

    public $mailer;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(OrderFulfilledMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  OrderWasFulfilled  $event
     * @return void
     */
    public function handle(OrderWasFulfilled $event)
    {
        $email = filter_var($event->order->buyer_email, FILTER_VALIDATE_EMAIL);
        // if email is valid send notification
        if ($email) {
            $event->order->load(['lines', 'tracking']);
            $this->mailer->sendNotification($event->order);
        }
    }
}
