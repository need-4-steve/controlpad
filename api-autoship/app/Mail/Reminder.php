<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Reminder extends Mailable
{
    use SerializesModels;
    public $buyer;
    public $settings;
    public $subscription;

    /**
     * Create a new message for the user to update card info.
     *
     * @return void
     */
    public function __construct($buyer, $subscription, $settings)
    {
        $this->buyer = $buyer;
        $this->settings = $settings;
        $this->subscription = $subscription;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reminder')
            ->from($this->settings->from_email->value)
            ->to($this->buyer->email)
            ->subject('A friendly reminder')
            ->with([
                'buyer' => $this->buyer,
                'subscription' => $this->subscription,
                'settings' => $this->settings,
            ]);
    }
}
