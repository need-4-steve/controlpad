<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Settings\SettingsService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;

class CardUpdate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message for the user to update card info.
     *
     * @return void
     */
    public function __construct(User $user, $fromEmail, $fromName, $card_update)
    {
        $this->user = $user;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->card_update = $card_update;
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
            $varables = $this->emailService->emailVar('card_update');
            $request = ['first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value')
            ];
            $body = $this->textService->parseText($request, $varables, $this->card_update->body);
            $this->card_update->subject = $this->textService->parseText($request, $varables, $this->card_update->subject);
            return $this->from($this->fromEmail, $this->fromName)
                        ->subject($this->card_update->subject)
                        ->view('emails.standard')
                        ->with(['body' =>$body]);
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
