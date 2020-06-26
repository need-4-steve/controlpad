<?php

namespace App\Mailers;

use Mail;
use App\Models\Order;
use App\Models\User;
use App\Models\CustomEmail;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;

class OrderFulfilledMailer
{
    public function __construct(EmailService $emailService, TextService $textService)
    {
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;
    }

    public function sendNotification($order)
    {
        if ($order['type_id'] === 10) {
            return;
        }
        $fulfilled_email = CustomEmail::where('title', 'fulfilled')->first();
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $varables = $this->emailService->emailVar('fulfilled');

        $request = ['first_name' => $order['buyer_first_name'],
                    'last_name' => $order['buyer_last_name'],
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                    'orderlines' => $order['lines'],
                    'order_receipt_id' => $order['receipt_id'],
                    'order_subtotal' => $order['subtotal_price'],
                    'order_tax' => $order['total_tax'],
                    'order_shipping' => $order['total_shipping'],
                    'order_discount' => $order['total_discount'],
                    'order_total' => $order['total_price']
        ];
        if (isset($order['tracking'][0])) {
            $tracking = $order['tracking'][0];
            $request['tracking_number'] = $tracking['number'];
            $request['tracking_url'] = $tracking['url'];
            $request['tracking_link'] = sprintf('<a href=“%s”>%s</a>', $tracking['url'], $tracking['number']);
        } else {
            $request['tracking_number'] = 'Tracking not set';
            $request['tracking_url'] = '';
            $request['tracking_link'] = 'Tracking not set';
        }

        $body = $this->textService->parseText($request, $varables, $fulfilled_email->body);
        $fulfilled_email->subject = $this->textService->parseText($request, $varables, $fulfilled_email->subject);
        $boundData = [
            'body' => $body
        ];
        if ($fulfilled_email->send_email) {
            $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            Mail::send('emails.standard', $boundData, function ($message) use ($order, $fromEmail, $fromName, $fulfilled_email) {
                $message->from($fromEmail, $fromName);
                $message->to($order['buyer_email'], $order['buyer_first_name'] . ' ' . $order['buyer_last_name'])
                        ->subject($fulfilled_email->subject);
            });
        }
    }
}
