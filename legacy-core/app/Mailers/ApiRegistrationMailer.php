<?php

namespace App\Mailers;

use Mail;
use App\Models\RegistrationToken;

class ApiRegistrationMailer
{
    public function __construct()
    {
    }

    public function sendRegistration(RegistrationToken $token)
    {
        // send an email to the new user to complete registration
        Mail::send('emails.mcommRegistrationEmail', ['token' => $token], function ($body) use ($token) {
            $body->from('no-reply@'. config('site.domain'));
            $body->to($token->email, $token->first_name)->subject('Welcome!');
        });
    }
}
