<?php

namespace App\Mailers;

use Mail;
use App\Models\User;
use App\Services\Settings\SettingsService;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use App\Models\CustomEmail;

class PasswordNewMailer
{
    public function __construct()
    {
        $this->settings = app('globalSettings');
        $this->emailService = new EmailService;
        $this->textService = new TextService;
    }

    public function sendPasswordNew(User $user)
    {
        $newPassword = CustomEmail::where('title', 'new_password')->first();
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $varables = $this->emailService->emailVar('new_password');
        $request = ['first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value')
        ];
        $body = $this->textService->parseText($request, $varables, $newPassword->body);
        $newPassword->subject = $this->textService->parseText($request, $varables, $newPassword->subject);
        $boundData = [
            'body' => $body
        ];
        if ($fromEmail === null) {
            $fromEmail = "no-reply@" . config('site.domain');
        }
        if ($newPassword->send_email) {
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send('emails.standard', $boundData, function ($message) use ($user, $fromEmail, $fromName, $newPassword) {
                $message->from($fromEmail, $fromName);
                $message->to($user->email, $user->full_name)
                        ->subject($newPassword->subject);
            });
        }
    }
}
