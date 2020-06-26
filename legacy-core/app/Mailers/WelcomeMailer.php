<?php

namespace App\Mailers;

use Log;
use Mail;
use App\Models\Order;
use App\Models\User;
use App\Models\CustomEmail;
use App\Services\Email\EmailService;
use App\Services\Text\TextService;
use CPCommon\Pid\Pid;
use App\Models\Coupon;

use Swift_TransportException;
use Exception;

class WelcomeMailer
{
    public function __construct(EmailService $emailService, TextService $textService)
    {
        $this->settings = app('globalSettings');
        $this->emailService = $emailService;
        $this->textService = $textService;
    }

    public function sendWelcome(User $user, ?Order $order = null)
    {
        $welcome = CustomEmail::where('title', 'welcome_email')->first();
        $newRep = CustomEmail::where('title', 'new_rep')->first();
        $varables = $this->emailService->emailVar('welcome_email');
        $request = ['first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'company_name' => $this->settings->getGlobal('company_name', 'value'),
                    'back_office_logo' => $this->settings->getGlobal('back_office_logo', 'value'),
        ];
        // Add coupon to registration email if setting is on.
        if ($this->settings->getGlobal('registration_coupon', 'show')) {
            try {
                $request['coupon_code'] = '';
                $couponAmount = $this->settings->getGlobal('registration_coupon', 'value');
                $description = 'Registration Coupon';
                if (empty($couponAmount) && isset($order)) {
                    $couponAmount = $order->subtotal_price;
                    if (isset($order->lines[0]->name)) {
                        $description = $order->lines[0]->name;
                    }
                }
                if (!empty($couponAmount)) {
                    do {
                        $couponCode = Pid::create();
                        $coupon = Coupon::where('code', $couponCode)->first();
                    } while (isset($coupon));
                    $corporate = User::select('id', 'pid')->where('id', config('site.apex_user_id'))->first();
                    $coupon = Coupon::create([
                        'code' => $couponCode,
                        'owner_id' => $corporate->id,
                        'owner_pid' => $corporate->pid,
                        'amount' => $couponAmount,
                        'is_percent' => false,
                        'title' => $user->first_name.' '.$user->last_name.' '.$user->id,
                        'description' => $description,
                        'max_uses' => 1,
                        'expires_at' => null,
                        'type' => 'wholesale',
                    ]);
                    $request['coupon_code'] = $coupon->code;
                } else {
                    logger()->error('Coupon code not generated on signup', ['user' => $user, 'order' => $order]);
                }
            } catch (\Exception $e) {
                logger()->error('Coupon code not generated on signup', ['user' => $user, 'order' => $order, 'exception' => $e]);
            }
        }
        $bodyWelcome = $this->textService->parseText($request, $varables, $welcome->body);
        $welcome->subject = $this->textService->parseText($request, $varables, $welcome->subject);
        $bodyRep = $this->textService->parseText($request, $varables, $newRep->body);
        $newRep->subject = $this->textService->parseText($request, $varables, $newRep->subject);

        $boundData = [
            'body' => $bodyWelcome
        ];
        $repData = [
            'body' => $bodyRep
        ];
        $fromEmail = $this->settings->getGlobal('from_email', 'value');
        $companyEmail = $this->settings->getGlobal('company_email', 'value');
        if ($fromEmail === null) {
            $fromEmail = "no-reply@" . config('site.domain');
        }
        if ($companyEmail === null) {
            $fromEmail = config('site.customer_service_email');
        }
        $fromName = env('MAIL_FROM_NAME', $this->settings->getGlobal('company_name', 'value'));

        try {
            if ($welcome->send_email) {
                Mail::send('emails.standard', $boundData, function ($message) use ($user, $fromEmail, $welcome, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($user->email, $user->full_name)
                            ->subject($welcome->subject);
                });
            }
            if ($newRep->send_email) {
                Mail::send('emails.standard', $repData, function ($message) use ($companyEmail, $fromEmail, $newRep, $fromName) {
                    $message->from($fromEmail, $fromName);
                    $message->to($companyEmail)->subject($newRep->subject);
                });
            }
        } catch (Swift_TransportException $e) {
            Log::error('Swift_TransportException - Unable to send welcome emails to: ' . $user->email);
            Log::error($e);
        } catch (Exception $e) {
            Log::error('Exception - Unable to send welcome emails to: ' . $user->email);
            Log::error($e);
        }
    }
}
