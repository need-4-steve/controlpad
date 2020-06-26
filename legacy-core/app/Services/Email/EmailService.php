<?php

namespace App\Services\Email;

use Mail;
use App\Models\Order;
use App\Models\User;
use App\Models\CustomEmail;
use App\Services\Text\TextService;
use Carbon\Carbon;
use DB;

class EmailService
{

    public function emailVar($title)
    {
        $varables = ['[first_name]', '[last_name]', '[company_name]', '[back_office_logo]', '[company_address]'];
        switch ($title) {
            case 'sponsor_notice':
                $varables = array_merge($varables, ['[sponsor_first_name]', '[sponsor_last_name]', '[email]', '[phone]']);
                break;
            case 'invoice':
                $varables = array_merge($varables, ['[amount]', '[invoice_url]', '[note]']);
                break;
            case 'fulfilled':
                $varables =  array_merge($varables, ['[orderlines]', '[order_receipt_id]', '[order_subtotal]', '[order_tax]', '[order_shipping]', '[order_discount]', '[order_total]', '[tracking_number]', '[tracking_url]', '[tracking_link]']);
                break;
            case 'expire_notice':
                $varables[] = '[billing_date]';
                break;
            case 'new_order_received':
                $varables = array_merge($varables, ['[customer_first_name]', '[customer_last_name]', '[customer_email]', '[orderlines]', '[order_receipt_id]']);
                break;
            case 'order_receipt':
                $varables = array_merge($varables, ['[orderlines]', '[order_receipt_id]', '[order_subtotal]', '[order_tax]', '[order_shipping]', '[order_discount]', '[order_total]']);
                break;
            case 'renew_fail':
                $varables = array_merge($varables, ['[billing_date]', '[reason]']);
                break;
            case 'welcome_email':
                if (app('globalSettings')->getGlobal('registration_coupon', 'show')) {
                    $varables = array_merge($varables, ['[coupon_code]']);
                }
                break;
            case 'Welcome_customer':
                $varables = ['[user_first_name]', '[user_last_name]', '[user_full_name]', '[user_email]', '[generated_password]', '[company_name]', '[backoffice_login_link]', '[back_office_logo]', '[company_address]'];
                break;
            case 'autoship_reminder':
            case 'autoship_sub_receipt':
            case 'autoship_sub_received':
            case 'autoship_sub_cancel':
            case 'autoship_sub_cancel_seller':
                $varables = ['[company_name]', '[backoffice_login_link]', '[back_office_logo]', '[company_address]', '[buyer_first_name]', '[buyer_last_name]', '[buyer_full_name]', '[buyer_email]', '[seller_full_name]', '[seller_email]', '[subscription_lines]', '[subscription_subtotal]', '[subscription_discount]', '[subscription_schedule]', '[subscription_next_billing_date]', '[subscription_id]'];
                break;
            default:
                $varables;
        }
        return $varables;
    }

    public function processNewOrders()
    {
        try {
            $lastOrderId = 0; // Helps paginate orders to balance db calls and memory loads
            $minDate = Carbon::now()->subDay()->toDateTimeString();
            // Only process up to existing records(max order_id). Prevents processing forever under load(overlap case)
            $maxId = DB::table('order_process')->max('order_id');
            if (empty($maxId)) {
                return;
            }
            do {
                $orderIds = DB::table('order_process')
                        ->select('order_id')->where('order_id', '>', $lastOrderId)
                        ->whereNull('emails_sent')->where('paid_at', '>', $minDate)
                        ->where('order_id', '<=', $maxId)->limit(100)->get()->pluck('order_id');
                $lastOrderId = $orderIds->last();
                foreach ($orderIds as $key => $orderId) {
                    $this->sendNewOrderEmails($orderId);
                }
            } while ($orderIds !== null && $orderIds->isNotEmpty() && $lastOrderId < $maxId);
        } catch (\Exception $e) {
            app('log')->error($e);
        }
    }

    private function sendNewOrderEmails($orderId)
    {
        try {
            $settings = app('globalSettings');
            $textService = new TextService;
            $order = Order::where('id', '=', $orderId)->with('lines')->first();
            if ($order->type_id === 10) {
                // Skip personal use orders, mark them as processed so we don't have to do this check farther up the chain
                DB::update('UPDATE order_process SET emails_sent = NOW() WHERE order_id = ?', [$orderId]);
                return;
            }
            $rep_notice_email = CustomEmail::where('title', 'new_order_received')->first();
            $order_receipt = CustomEmail::where('title', 'order_receipt')->first();
            $orderlines = $order->lines;
            $userStore = User::select('id', 'first_name', 'last_name', 'email')->where('id', $order->store_owner_user_id)->first();
            $fromEmail = $settings->getGlobal('from_email', 'value');
            $varablesReceipt = $this->emailVar('order_receipt');
            $varablesRepNotice = $this->emailVar('new_order_received');
            $requestReceipt = ['first_name' => $order->buyer_first_name,
                                'last_name' => $order->buyer_last_name,
                                'company_name' => $settings->getGlobal('company_name', 'value'),
                                'back_office_logo' => $settings->getGlobal('back_office_logo', 'value'),
                                'orderlines' => $orderlines,
                                'order_receipt_id' => $order->receipt_id,
                                'order_subtotal' => $order->subtotal_price,
                                'order_tax' => $order->total_tax,
                                'order_shipping' => $order->total_shipping,
                                'order_discount' => $order->total_discount,
                                'order_total' => $order->total_price
            ];
            $requestRepNotice = ['first_name' => $userStore->first_name,
                                'last_name' => $userStore->last_name,
                                'company_name' => $settings->getGlobal('company_name', 'value'),
                                'back_office_logo' => $settings->getGlobal('back_office_logo', 'value'),
                                'customer_first_name' => $order->buyer_first_name,
                                'customer_last_name' => $order->buyer_last_name,
                                'customer_email' => $order->buyer_email,
                                'orderlines' => $orderlines,
                                'order_receipt_id' => $order->receipt_id,
                                'order_subtotal' => $order->subtotal_price,
                                'order_tax' => $order->total_tax,
                                'order_shipping' => $order->total_tax,
                                'order_discount' => $order->total_discount,
                                'order_total' => $order->total_price
            ];

            $bodyReceipt = $textService->parseText($requestReceipt, $varablesReceipt, $order_receipt->body);
            $order_receipt->subject = $textService->parseText($requestReceipt, $varablesReceipt, $order_receipt->subject);
            $bodyNotice = $textService->parseText($requestRepNotice, $varablesRepNotice, $rep_notice_email->body);
            $rep_notice_email->subject = $textService->parseText($requestRepNotice, $varablesRepNotice, $rep_notice_email->subject);
            if ($userStore->id === 1) {
                $userStore->email = $settings->getGlobal('order_notification_email', 'value');
                $fromName = env('MAIL_FROM_NAME', $settings->getGlobal('company_name', 'value'));
            } else {
                $fromName = $userStore->full_name . ' - ' . $settings->getGlobal('company_name', 'value');
            }

            $boundData = [
                'body' => $bodyReceipt
            ];

            $storeData = [
                'body' => $bodyNotice
            ];
            if ($order_receipt->send_email) {
                Mail::send('emails.standard', $boundData, function ($message) use ($order, $orderlines, $fromEmail, $order_receipt, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($order->buyer_email, $order->buyer_first_name . ' ' . $order->buyer_last_name)->subject($order_receipt->subject);
                });
            }
            if ($rep_notice_email->send_email) {
                Mail::send('emails.standard', $storeData, function ($message) use ($userStore, $order, $orderlines, $fromEmail, $rep_notice_email) {
                    $message->from($fromEmail, 'You have a new order');
                    $message->to($userStore->email, $userStore->full_name)->subject($rep_notice_email->subject);
                });
            }
            DB::update('UPDATE order_process SET emails_sent = NOW() WHERE order_id = ?', [$orderId]);
        } catch (\Exception $e) {
            logger($e);
        }
    }
}
