<?php

namespace App\Listeners;

use Log;
use App\Events\ExpiredSubscripitionRenew;
use App\Mail\SubscriptionRenewed;
use App\Mail\SubscriptionFailed;
use App\Models\CustomEmail;
use App\Models\SubscriptionReceipt;
use App\Models\User;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Services\Payman\PayManService;
use App\Services\Tax\TaxService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class ExpiredSubscripitionRenewListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SubscriptionRepository $subscriptionRepo)
    {
        $this->paymentManager = new PayManService;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->taxService = new TaxService;
        $this->authRepo = new AuthRepository;
        $this->settings = app('globalSettings');
    }

    /**
     * Handle the event.
     *
     * @param  SomeEvent  $event
     * @return void
     */
    public function handle(ExpiredSubscripitionRenew $event)
    {
        $renew_success = CustomEmail::where('title', 'renew_success')->first();
        $renew_fail = CustomEmail::where('title', 'renew_fail')->first();
        $event->subscriptionData['total_tax'] = 0;
        // find subscription taxes
        if ($this->settings->getGlobal('tax_subscription', 'show')) {
            $user = $event->subscriptionData->user;
            $user->load('billingAddress', 'shippingAddress');
            $companyUser = User::select('pid')->where('id', '=', config('site.apex_user_id'))->first();
            $taxInvoice = $this->taxService->createSubscriptionTaxInvoice(
                $user->billingAddress,
                $user->shippingAddress,
                $this->authRepo->getCorporateBusinessAddress(),
                $event->subscriptionData,
                1,
                $companyUser->pid,
                false
            );
            if (isset($taxInvoice->error)) {
                Log::error(json_encode([
                    'message' => 'Failed to create tax invoice for subscription user: ' . $user->id,
                    'response' => $taxInvoice
                ]));
                return;
            } else {
                $event->subscriptionData['total_tax'] = $taxInvoice->tax;
                $taxInvoicePid = $taxInvoice->pid;
            }
        }

        $payment = $this->paymentManager->subCreditCard($event->subscriptionData, $event->subscriptionData, $event->subscriptionData->user->full_name);
        if ($payment['success'] === false) {
            $this->subscriptionRepo->updateUserSubscriptonOnFail($event->subscriptionData->user->id, $payment['description']);
            if ($renew_fail->send_email) {
                Mail::to($event->subscriptionData->user->email)->send(new SubscriptionFailed($event->subscriptionData, $payment['description'], $renew_fail));
            }
        } else {
            $receipt = $this->subscriptionRepo->createSubscriptionReceipt(
                $event->subscriptionData,
                $payment,
                $event->subscriptionData->user_id,
                isset($taxInvoicePid) ? $taxInvoicePid : null
            );
            if (isset($taxInvoicePid)) {
                $this->taxService->queueTaxInvoiceCommit($taxInvoicePid, 'Sub:'.$receipt->id);
            }
            $this->subscriptionRepo->updateUserSubscription($event->subscriptionData->user, $event->subscriptionData);
            if ($renew_success->send_email) {
                Mail::to($event->subscriptionData->user->email)->send(new SubscriptionRenewed($event->subscriptionData->user, $renew_success));
            }
        }
    }
}
