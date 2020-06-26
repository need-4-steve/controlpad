<?php

namespace App\Mailers;

use Mail;
use Carbon\Carbon;
use App\Models\CustomEmail;
use App\Models\UserSetting;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;

class AutoshipSubCancelMailer
{
    public function __construct(EmailService $emailService, TextService $textService)
    {
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;
    }

    public function sendNotification($subscription)
    {
        $cancelEmailBuyer = CustomEmail::where('title', 'autoship_sub_cancel')->first();
        $cancelEmailSeller = CustomEmail::where('title', 'autoship_sub_cancel_seller')->first();
        if (!$cancelEmailBuyer->send_email && !$cancelEmailSeller->send_email) {
            return;
        }
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $sellerName = $subscription['seller']['first_name'] . ' ' . $subscription['seller']['first_name'];
        $sellerEmail = $subscription['seller']['email'];
        $buyerEmail = $subscription['buyer']['email'];
        $buyerName = $subscription['buyer']['first_name'] . ' ' . $subscription['buyer']['last_name'];

        $varables = $this->emailService->emailVar($cancelEmailBuyer->title);
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
        // subscription_next_billing_date is none
        $request['subscription_next_billing_date'] = 'None';

        if ($cancelEmailBuyer->send_email) {
            $body = $this->textService->parseText($request, $varables, $cancelEmailBuyer->body);
            $cancelEmailBuyer->subject = $this->textService->parseText($request, $varables, $cancelEmailBuyer->subject);
            $boundData = [
                'body' => $body
            ];
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send(
                'emails.standard',
                $boundData,
                function ($message) use ($subscription, $fromEmail, $fromName, $cancelEmailBuyer, $buyerEmail, $buyerName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($buyerEmail, $buyerName)
                    ->subject($cancelEmailBuyer->subject);
                }
            );
        }

        if ($cancelEmailSeller->send_email) {
            $body = $this->textService->parseText($request, $varables, $cancelEmailSeller->body);
            $cancelEmailSeller->subject = $this->textService->parseText($request, $varables, $cancelEmailSeller->subject);
            $boundData = [
                'body' => $body
            ];
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send(
                'emails.standard',
                $boundData,
                function ($message) use ($subscription, $fromEmail, $fromName, $cancelEmailSeller, $sellerEmail, $sellerName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($sellerEmail, $sellerName)
                    ->subject($cancelEmailSeller->subject);
                }
            );
        }
    }
}
