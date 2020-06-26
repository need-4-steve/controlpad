<?php

namespace App\Mailers;

use Mail;
use CPCommon\Pid\Pid;
use App\Models\CustomEmail;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;

use Swift_TransportException;
use Exception;

class WelcomeCustomerMailer
{
    public function __construct(EmailService $emailService, TextService $textService)
    {
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;
    }

    public function sendNotification($user)
    {
        try {
            $welcomeEmail = CustomEmail::where('title', 'welcome_customer')->first();

            if ($welcomeEmail) {
                $varables = $this->emailService->emailVar('welcome_customer');
                $companyName = $this->settings->getGlobal('company_name', 'value');
                // TODO generated_password (user friendly strong password)?
                $request = [
                    'user_first_name' => $user['first_name'],
                    'user_last_name' => $user['last_name'],
                    'user_full_name' => $user['first_name'].' '.$user['last_name'],
                    'user_email' => $user['email'],
                    'company_name' => $companyName,
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                    'backoffice_login_link' => env('APP_URL').'/login',
                    'generated_password' => substr(Pid::create(), 0, 12),
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value')
                ];
                $bodyWelcome = $this->textService->parseText($request, $varables, $welcomeEmail->body);
                $welcomeEmail->subject = $this->textService->parseText($request, $varables, $welcomeEmail->subject);
                $boundData = [
                    'body' => $bodyWelcome
                ];
                $fromEmail = $this->settings->getGlobal('from_email', 'value');
                $companyEmail = $this->settings->getGlobal('company_email', 'value');
                if ($fromEmail === null) {
                    $fromEmail = "no-reply@" . config('site.domain');
                }
                if ($companyEmail === null) {
                    $fromEmail = config('site.customer_service_email');
                }
                $fromName = env('MAIL_FROM_NAME', $companyName);

                Mail::send('emails.standard', $boundData, function ($message) use ($user, $fromEmail, $welcomeEmail, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($user->email, $user->full_name)
                    ->subject($welcomeEmail->subject);
                });
            }
        } catch (Swift_TransportException $e) {
            Log::error('Swift_TransportException - Unable to send welcome customer email to: ' . $user->email);
            Log::error($e);
        } catch (Exception $e) {
            Log::error('Exception - Unable to send welcome customer email to: ' . $user->email);
            Log::error($e);
        }
    }
}
