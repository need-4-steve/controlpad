<?php

namespace App\Mailers;

use Mail;
use Carbon\Carbon;
use App\Models\CustomEmail;
use App\Models\UserSetting;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;

class AutoshipSubCreatedMailer
{
    public function __construct(EmailService $emailService, TextService $textService)
    {
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;
    }

    public function sendNotification($subscription)
    {
        $receiptEmail = CustomEmail::where('title', 'autoship_sub_receipt')->first();
        $receivedEmail = CustomEmail::where('title', 'autoship_sub_received')->first();
        if (!$receiptEmail->send_email && !$receivedEmail->send_email) {
            return;
        }
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $sellerName = $subscription['seller']['first_name'] . ' ' . $subscription['seller']['first_name'];
        $sellerEmail = $subscription['seller']['email'];
        $buyerEmail = $subscription['buyer']['email'];
        $buyerName = $subscription['buyer']['first_name'] . ' ' . $subscription['buyer']['last_name'];

        $varables = $this->emailService->emailVar($receiptEmail->title);
        $request = ['buyer_first_name' => $subscription['buyer']['first_name'],
                    'buyer_last_name' => $subscription['buyer']['last_name'],
                    'buyer_full_name' => $buyerName,
                    'buyer_email' => $buyerEmail,
                    'seller_full_name' => $sellerName,
                    'seller_email' => $sellerEmail,
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                    'backoffice_login_link' => env('APP_URL').'/login',
                    'subscription_id' => $subscription['id'],
                    'subscription_lines' => $subscription['lines'],
                    'subscription_subtotal' => $subscription['subtotal'],
                    'subscription_discount' => $subscription['discount']
        ];
        // Convert schedule
        if ($subscription['frequency'] == 1) {
            $request['subscription_schedule'] = strval($subscription['frequency']) . ' ' . str_replace('s', '', $subscription['duration']);
        } else {
            $request['subscription_schedule'] = strval($subscription['frequency']) . ' ' . $subscription['duration'];
        }
        // Convert subscription_next_billing_date
        $companySettings = UserSetting::where('user_id', config('site.apex_user_id'))->first();
        $timezone = $companySettings == null ? 'UTC' : $companySettings->timezone;

        $request['subscription_next_billing_date'] = Carbon::parse($subscription['next_billing_at'], 'UTC')
            ->setTimezone($timezone)->format('M d Y');

        if ($receiptEmail->send_email) {
            $body = $this->textService->parseText($request, $varables, $receiptEmail->body);
            $receiptEmail->subject = $this->textService->parseText($request, $varables, $receiptEmail->subject);
            $boundData = [
                'body' => $body
            ];
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send('emails.standard', $boundData, function ($message) use ($subscription, $fromEmail, $fromName, $receiptEmail, $buyerEmail, $buyerName) {
                $message->from($fromEmail, $fromName);
                $message->to($buyerEmail, $buyerEmail)
                ->subject($receiptEmail->subject);
            });
        }

        if ($receivedEmail->send_email) {
            $body = $this->textService->parseText($request, $varables, $receivedEmail->body);
            $receivedEmail->subject = $this->textService->parseText($request, $varables, $receivedEmail->subject);
            $boundData = [
                'body' => $body
            ];
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send(
                'emails.standard',
                $boundData,
                function ($message) use ($subscription, $fromEmail, $fromName, $receivedEmail, $sellerEmail, $sellerName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($sellerEmail, $sellerName)
                    ->subject($receivedEmail->subject);
                }
            );
        }
    }
}
