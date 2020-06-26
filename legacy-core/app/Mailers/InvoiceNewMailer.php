<?php

namespace App\Mailers;

use Mail;
use App\Models\Invoice;
use App\Models\User;
use App\Models\CustomEmail;
use App\Models\Note;
use App\Services\Email\EmailService;
use App\Services\Settings\SettingsService;
use App\Services\Text\TextService;

class InvoiceNewMailer
{
    public function __construct()
    {
        $this->settings = app('globalSettings');
        $this->emailService = new EmailService;
        $this->textService = new TextService;
    }
    public function sendInvoice(Invoice $invoice)
    {
        if (! $invoice->relationLoaded('user')) {
            $invoice->load('user');
        }

        if (! $invoice->relationLoaded('items')) {
            $invoice->load('invoiceItems.product');
        }
        if (! $invoice->relationLoaded('price')) {
            $invoice->load('invoiceItems.msrp');
        }
        $notes = Note::where('noteable_type', 'App\Models\Invoice')
                    ->where('noteable_id', $invoice->id)
                    ->first();
        $invoice_email = CustomEmail::where('title', 'invoice')->first();
        $varables = $this->emailService->emailVar('invoice');
        $request = ['first_name' => $invoice->user->first_name,
                    'last_name' => $invoice->user->last_name,
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
                    'amount' => $invoice->subtotal_price,
                    'invoice_url' => config('app.url').'/orders/invoice/'.$invoice->token,
                    'note' => isset($notes->body) ? $notes->body : ''
                ];
        $body = $this->textService->parseText($request, $varables, $invoice_email->body);
        $invoice_email->subject = $this->textService->parseText($request, $varables, $invoice_email->subject);
        if ($invoice_email->send_email) {
            $fromEmail = $this->settings->getGlobal('from_email', 'value');
            if ($invoice->store_owner_user_id === 1) {
                $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));
            } else {
                $fromName = $invoice->owner->full_name . ' - ' . $this->settings->getGlobal('company_name', 'value');
            }
            Mail::send('emails.standard', ['body' => $body], function ($message) use ($invoice, $invoice_email, $fromEmail, $fromName) {
                $message->from($fromEmail, $fromName);
                $message->to(
                    $invoice->user->email,
                    $invoice->user->full_name
                )->subject($invoice_email->subject);
            });
        }
    }
}
