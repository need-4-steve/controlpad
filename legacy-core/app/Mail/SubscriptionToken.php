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

class SubscriptionToken extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a message for subscription that do not have
     * a card token.
     *
     * @return void
     */
    public function __construct(User $user, $fromEmail, $fromName, $missing_card)
    {
        $this->user = $user;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->missing_card = $missing_card;
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
            $varables = $this->emailService->emailVar('missing_card');
            $request = ['first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
            ];

            $body = $this->textService->parseText($request, $varables, $this->missing_card->body);
            $this->missing_card->subject = $this->textService->parseText($request, $varables, $this->missing_card->subject);
            return $this->from($this->fromEmail, $this->fromName)
                        ->subject($this->missing_card->subject)
                        ->view('emails.standard')
                        ->with(['body' => $body]);
        } catch (Exception $e) {
            logger($e);
        }
    }
}
