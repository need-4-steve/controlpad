<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Failure extends Mailable
{
    use SerializesModels;
    public $buyer;
    public $failureMessage;
    public $settings;
    public $subscription;

    /**
     * Create a new message for the user to update card info.
     *
     * @return void
     */
    public function __construct($buyer, $subscription, $settings, $failureMessage = null)
    {
        $this->buyer = $buyer;
        $this->subscription = $subscription;
        $this->settings = $settings;
        $this->failureMessage = $failureMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.failure')
            ->from($this->settings->from_email->value)
            ->to($this->buyer->email)
            ->subject($this->settings->autoship_display_name->value.' Order Failure')
            ->with([
                'buyer' => $this->buyer,
                'subscription' => $this->subscription,
                'settings' => $this->settings,
                'failureMessage' => $this->failureMessage
            ]);
    }
}
