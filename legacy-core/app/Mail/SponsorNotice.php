<?php

namespace App\Mail;

use App\Models\User;
use App\Models\CustomEmail;
use App\Services\Settings\SettingsService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SponsorNotice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $fromEmail)
    {
        $this->user = $user;
        $this->fromEmail = $fromEmail;
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
            $sponsor = CustomEmail::where('title', 'sponsor_notice')->first();
            $variables = $this->emailService->emailVar('sponsor_notice');
            $request = ['first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'company_name' => $this->settings->getGlobal('company_name', 'value'),
                        'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                        'sponsor_first_name' => $this->user->sponsor->first_name,
                        'sponsor_last_name' => $this->user->sponsor->last_name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone_number,
            ];
            $body = $this->textService->parseText($request, $variables, $sponsor->body);
            $sponsor->subject = $this->textService->parseText($request, $variables, $sponsor->subject);
            return $this->from($this->fromEmail)
                        ->subject($sponsor->subject)
                        ->view('emails.standard')
                        ->with(['body' => $body]);
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
