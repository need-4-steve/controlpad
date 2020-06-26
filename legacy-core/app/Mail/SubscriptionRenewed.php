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

class SubscriptionRenewed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message for subscription that have been renewed.
     *
     * @return void
     */
    public function __construct(User $user, $renew_success)
    {
        $this->user = $user;
        $this->renew_success = $renew_success;
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
            $varables = $this->emailService->emailVar('renew_success');
            $request = ['first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value')
            ];
            $body = $this->textService->parseText($request, $varables, $this->renew_success->body);
            $this->renew_success->subject = $this->textService->parseText($request, $varables, $this->renew_success->subject);
            return $this->subject($this->renew_success->subject)
                        ->view('emails.standard')
                        ->with(['body' => $body]);
        } catch (Exception $e) {
            logger($e);
        }
    }
}
