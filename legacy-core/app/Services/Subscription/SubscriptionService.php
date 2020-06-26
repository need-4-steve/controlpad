<?php

namespace App\Services\Subscription;

use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Services\PayMan\PayManService;
use App\Services\Tax\TaxService;
use App\Events\ExpiredSubscripitionRenew;
use App\Events\SubscriptionExpireNotification;
use App\Events\SubscriptionCardNotification;
use App\Events\SubscriptionCardUpdate;
use App\Models\Subscription;
use App\Models\SubscriptionReceipt;
use App\Models\SubscriptionUser;
use App\Models\TaxInvoice;
use Carbon\Carbon;

class SubscriptionService
{
    protected $subscriptionRepo;
    protected $payManService;

    public function __construct(
        AuthRepository $authRepo,
        SubscriptionRepository $subscriptionRepo
    ) {
        $this->authRepo = $authRepo;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->paymanService = new PaymanService;
    }

    public function renewExpiredSubscriptions()
    {
        $expiredSubscriptions = $this->subscriptionRepo->expiredSubscriptions();
        foreach ($expiredSubscriptions as $subscription) {
            event(new ExpiredSubscripitionRenew($subscription));
        }
        return $expiredSubscriptions;
    }

    public function renewExpiredFreeSubscriptions()
    {
        $expiredSubscriptions = $this->subscriptionRepo->expiredFreeSubscriptions();
        foreach ($expiredSubscriptions as $subscription) {
            try {
                $this->subscriptionRepo->updateUserSubscription($subscription->user, $subscription);
            } catch (Exception $e) {
                logger()->error($e);
            }
        }
        return $expiredSubscriptions;
    }

    public function userRenew($user_id)
    {
        $user = $this->subscriptionRepo->renewForUser($user_id);
        $subAmount = $this->subscriptionRepo->renewAmount(
            $user_id,
            $this->authRepo->getCorporateBusinessAddress()
        );
        if (isset($subAmount['error'])) {
            return $subAmount;
        }
        $sub = $user['userSubscription'];
        $sub->subscription_user_id = $user['userSubscription']['id'];
        $sub->id = $sub->subscription_id;
        $sub->duration = $subAmount['months'];
        $token = $user['token'];
        if ($subAmount['subtotal_price'] > 0.0) {
            if ($token == null) {
                return ['error' => true, 'message' => 'We do not have a card on file'];
            }
            $paymentProcess = $this->paymanService->subCreditCard($subAmount, $token);
            if ($paymentProcess['success'] === false) {
                $this->subscriptionRepo->updateUserSubscriptonOnFail($user_id, $paymentProcess['description']);
                return ['error' => true, 'message' => $paymentProcess['description']];
            }
            $receipt = $this->subscriptionRepo->createSubscriptionReceipt($sub, $paymentProcess, $user_id, $subAmount['tax_invoice_pid']);
            if ($subAmount['tax_invoice_pid'] != null) {
                (new TaxService)->queueTaxInvoiceCommit($subAmount['tax_invoice']->pid, 'Sub:'.$receipt->id);
            }
        }
        $sub->ends_at = Carbon::parse($subAmount['expires_at']);
        $wasDisabled = !empty($sub->disabled_at);
        $sub->disabled_at = null;
        unset($sub->id);
        unset($sub->duration);
        unset($sub->subscription_user_id);
        $sub->save();
        if ($wasDisabled) {
            event(new \App\Events\SubscriptionRenewedEvent($sub->user, $sub));
        }

        return $sub;
    }

    public function subscriptionsNotification()
    {
        $subscriptions =  $this->subscriptionRepo->nearExpiredSubscriptons();
        foreach ($subscriptions as $subscription) {
            event(new SubscriptionExpireNotification($subscription));
        }
        return $subscriptions;
    }

    public function subscriptionsWithOutCards()
    {
        $subscriptions = $this->subscriptionRepo->subscriptonsWithOutCard();
        foreach ($subscriptions as $subscription) {
            event(new SubscriptionCardNotification($subscription));
        }
        return $subscriptions;
    }

    public function subscriptionsBadCard()
    {
        $subscriptions = $this->subscriptionRepo->expiredSubscriptionBadCard();
        foreach ($subscriptions as $subscription) {
            event(new SubscriptionCardUpdate($subscription->user));
        }
        return $subscriptions;
    }

    public function calculateTransactions($request)
    {
        $subscriptions = $this->subscriptionRepo->reportTransactions($request);
        $count = count($subscriptions);
        $amount =$subscriptions->sum('subtotal_price');
        $autoRenewcount = $this->subscriptionRepo->autoRenewCount();

        return ['count' => $count, 'amount' => $amount, 'autoCount' => $autoRenewcount];
    }

    public function renewUserForAdmin($user)
    {
        $token = collect(['token', 'gateway_customer_id', 'name']);
        $token->token = $user['token'];
        $token->gateway_customer_id = $user['gateway_customer_id'];
        $token->name = $user['first_name'] . ' ' . $user['last_name'];
        $paymentProcess = $this->paymanService->subCreditCard($user, $token, $token->name);
        if ($paymentProcess['success'] !== true) {
            return ['error' => true, 'message' => $paymentProcess['description']];
        }
        $subscriptionInfo = collect(['id', 'title', 'duration', 'subscription_user_id']);
        $subscriptionInfo->id = $user['subscription_id'];
        $subscriptionInfo->title = $user['title'];
        $subscriptionInfo->subscription_user_id = $user['id'];
        $monthsOver = Carbon::parse($user['billing_date'])->diffInMonths(Carbon::now());
        $duration = $monthsOver + 1;
        $subscriptionInfo->duration = $duration;
        $taxInvoicePid = isset($user['tax_invoice_pid']) ? $user['tax_invoice_pid'] : null;
        $receipt = $this->subscriptionRepo->createSubscriptionReceipt(
            $subscriptionInfo,
            $paymentProcess,
            $user['user_id'],
            $taxInvoicePid
        );
        if ($taxInvoicePid != null) {
            (new TaxService)->queueTaxInvoiceCommit($taxInvoicePid, 'Sub:'.$receipt->id);
        }
        $subscription = $this->subscriptionRepo->renewUserForAdmin($user);
        if (isset($subscription['error'])) {
            return ['error' => true, 'message' => $subscription['message']];
        } else {
            return $subscription;
        }
    }

    /**
    * Used for scheduler daily
    */
    public function disableExpiredSubscriptions()
    {
        $subs = $this->subscriptionRepo->findSubscriptionsForAutoDisable();
        foreach ($subs as &$sub) {
            $updated = $sub->update(['disabled_at' => Carbon::now()]);
            if ($updated) {
                event(new \App\Events\SubscriptionExpiredEvent($sub));
            }
        }
        return $subs;
    }
}
