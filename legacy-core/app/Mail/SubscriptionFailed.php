<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Settings\SettingsService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionFailed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message for subscriptions that have failed.
     *
     * @return void
     */
    public function __construct($subscription, String $reason, $renew_fail)
    {
         $this->user = $subscription->user;
         $this->subscription = $subscription;
         $this->reason = $reason;
         $this->renew_fail = $renew_fail;
         $this->settings = app('globalSettings');
         $this->emailService = new EmailService;
         $this->textService = new TextService;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            $varables = $this->emailService->emailVar('renew_fail');
            $request = ['first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                        'billing_date' =>  $this->subscription->ends_at->format('l, F d Y'),
                        'reason' => $this->reason
            ];
            $body = $this->textService->parseText($request, $varables, $this->renew_fail->body);
            $subject = $this->textService->parseText($request, $varables, $this->renew_fail->subject);
            return $this->subject($subject)
                        ->view('emails.standard')
                        ->with([
                            'body' => $body
                        ]);
        } catch (Exception $e) {
            logger($e);
        }
    }
}
