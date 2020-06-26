<?php

namespace App\Mail;

use App\Services\Settings\SettingsService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionExpireNotice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subscriptionUser, $fromEmail, $fromName, $notice)
    {
        $this->subscriptionUser = $subscriptionUser;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->notice =$notice;
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
            $varables = $this->emailService->emailVar('expire_notice');
            $request = ['first_name' => $this->subscriptionUser->user->first_name,
                        'last_name' => $this->subscriptionUser->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                        'billing_date' => $this->subscriptionUser->ends_at->format('l, F d Y')
            ];
            $body = $this->textService->parseText($request, $varables, $this->notice->body);
            $this->notice->subject = $this->textService->parseText($request, $varables, $this->notice->subject);
            return $this->from($this->fromEmail, $this->fromName)
                    ->subject($this->notice->subject)
                    ->view('emails.standard')
                    ->with(['body' => $body]);
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
