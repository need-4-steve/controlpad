<?php

namespace App\Repositories\Eloquent;

use App\Models\CardToken;
use App\Models\Subscription;
use App\Models\SubscriptionAttempt;
use App\Models\SubscriptionUser;
use App\Models\SubscriptionReceipt;
use App\Models\UserSetting;
use App\Models\User;
use App\Models\Price;
use App\Mail\SubscriptionRenewed;
use App\Mail\SubscriptionFailed;
use App\Mail\SubscriptionToken;
use App\Mail\CardUpdate;
use App\Repositories\Contracts\SubscriptionRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Services\PayMan\PayManService;
use App\Services\Tax\TaxService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SubscriptionRepository implements SubscriptionRepositoryContract
{
    use CommonCrudTrait;

    public function __construct(
        PayManService $payMan
    ) {
         $this->paymentManager = $payMan;
    }

    public function indexSignUp()
    {
        return Subscription::where('on_sign_up', 1)->with('price')->get();
    }

    public function find($id, $eagerLoad = [])
    {
        return Subscription::where('id', $id)
                ->with($eagerLoad)
                ->first();
    }

    public function create(array $inputs = [])
    {
        $subscription = new Subscription;
        if (!isset($inputs['duration']) || $inputs['duration'] === null) {
            $inputs['duration'] = 1;
        }
        if (!isset($inputs['on_sign_up']) || $inputs['on_sign_up'] ===null) {
            $inputs['on_sign_up'] = 0;
        }
        if (!isset($inputs['description']) || $inputs['description'] === null) {
            $inputs['description'] = ' ';
        }
        if (!isset($inputs['tax_class']) || $inputs['tax_class'] === null) {
            $inputs['tax_class'] = ' ';
        }

        $fields = ['title', 'duration', 'renewable','description', 'free_trial_time', 'on_sign_up', 'seller_type_id', 'tax_class', 'plan_price'];
        foreach ($fields as $field) {
            $subscription->$field = array_get($inputs, $field);
        }
        // replace characters that break things
        if (isset($subscription->description)) {
            $subscription->description = str_replace(['&quot'], ' ', $subscription->description);
            $subscription->description = str_replace(['}'], ' } ', $subscription->description);
            $subscription->description = str_replace(['{'], ' { ', $subscription->description);
        }
        $subscription->slug = str_slug(array_get($inputs, 'title', ''));
        $subscription->save();
        $subscription->price()->create([
            'price_type_id' => 1,
            'price' => (double)$inputs['price']['price'],
            'priceable_id' => $subscription['id']
        ]);

        return $subscription;
    }

    public function update(Subscription $subscription, array $inputs = [])
    {
        $fields = ['title', 'duration', 'renewable','description', 'free_trial_time', 'on_sign_up', 'seller_type_id', 'tax_class'];
        foreach ($fields as $field) {
            $subscription->$field = array_get($inputs, $field);
        }

        // replace characters that break things
        if (isset($subscription->description)) {
            $subscription->description = str_replace(['&quot'], ' ', $subscription->description);
            $subscription->description = str_replace(['}'], ' } ', $subscription->description);
            $subscription->description = str_replace(['{'], ' { ', $subscription->description);
        }

        $subscription->slug = str_slug(array_get($inputs, 'title', ''));
        $subscription->price->update([$inputs['price']]);
        $subscription->update();
        return $subscription;
    }
    public function allUserSubscriptions($request)
    {
        $userSubscriptions = SubscriptionUser::join('subscriptions', 'subscription_id', 'subscriptions.id')
        ->join('users', 'user_id', 'users.id')
        ->with('attempts')
        ->join('prices', 'subscription_id', 'prices.priceable_id')
            ->where('prices.priceable_type', 'App\Models\Subscription')
        ->leftJoin('card_token', 'subscription_user.user_id', '=', 'card_token.user_id')
        ->has('user')
        ->select(
            'subscription_user.*',
            'subscriptions.title',
            'users.first_name',
            'users.last_name',
            'prices.price',
            'token',
            'gateway_customer_id',
            'subscriptions.tax_class'
        );

        switch ($request['status']) {
            case 'active':
                $userSubscriptions = $userSubscriptions->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>', Carbon::now());
                });
                break;
            case 'expired':
                $userSubscriptions = $userSubscriptions->where('ends_at', '<', Carbon::now()->subDays(1));
                break;
            case 'expiring':
                $userSubscriptions = $userSubscriptions->whereBetween('ends_at', [Carbon::now(), Carbon::now()->addDays(8)]);
                break;
            case 'all':
                $userSubscriptions;
                break;
            default:
                $userSubscriptions;
        }

        if (! empty($request['search_term'])) {
            $userSubscriptions->where(function ($query) use ($request) {
                $query->where('subscription_user.user_id', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhere('first_name', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhere('last_name', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhere('title', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%".$request['search_term'] ."%')");
            });
        }
        if (isset($request['column'])) {
            $userSubscriptions->orderBy($request['column'], $request['order']);
        }
        if (! isset($request['per_page'])) {
            return $userSubscriptions->distinct()->get();
        }
        if (isset($request['start_date']) && isset($request['end_date'])) {
            $timezone = $this->getTimeZone();
            $startDate = Carbon::parse($request['start_date'], $timezone)
                                ->startOfDay()
                                ->setTimezone('UTC')
                                ->format('Y-m-d H:i:s');
            $endDate = Carbon::parse($request['end_date'], $timezone)
                                ->endOfDay()
                                ->setTimezone('UTC')
                                ->format('Y-m-d H:i:s');
            $userSubscriptions = $userSubscriptions->whereBetween('ends_at', [$startDate, $endDate]);
        }

        return $userSubscriptions->distinct()->paginate($request['per_page']);
    }

    public function updateAutoRenew($request)
    {
        $userSubscription = SubscriptionUser::find($request['id']);
        if ($request['auto_renew'] === false) {
            $userSubscription->update([
                'auto_renew' => $request['auto_renew'],
            ]);
        } else {
            if ($request['ends_at'] > Carbon::now()) {
                $userSubscription->update(['auto_renew' => $request['auto_renew']]);
            } else {
                $userSubscription->update([
                    'auto_renew' => $request['auto_renew'],
                    'ends_at' => Carbon::now()->toDateTimeString()
                ]);
            }
        }
        return $userSubscription;
    }

    public function renewForUser($user_id)
    {
        $userSubscription = SubscriptionUser::where('user_id', $user_id)
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscription_user.subscription_id')
                    ->where('prices.priceable_type', Subscription::class);
            })
            ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
            ->select('subscription_user.*', 'price', 'duration', 'title')
            ->first();

        if (!$userSubscription) {
            return ['error' => true, 'message' => 'No user subscription found.'];
        }
        $token = CardToken::where('user_id', $user_id)->where('type', 'subscription')->first();
        $userSubscription->fail_description = '';
        return ['userSubscription'=>$userSubscription, 'token'=>$token];
    }

    public function renewAmount($user_id, $company_address)
    {
        $now = Carbon::now();
        $userSubscription = SubscriptionUser::where('user_id', $user_id)
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscription_user.subscription_id')
                    ->where('prices.priceable_type', Subscription::class);
            })
            ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
            ->select('subscription_user.*', 'price', 'duration', 'title')
            ->first();
        if (! $userSubscription) {
            return false;
        }
        if (!empty($userSubscription->disabled_at)) {
            $expiresAt = Carbon::now()->addMonths($userSubscription->duration)->format('Y-m-d');
            $price = $userSubscription->price;
            $quantity = 1;
        } else {
            // Calculate extended end date
            $newDate = Carbon::parse($userSubscription->ends_at)
                ->addMonths($userSubscription->duration);
            // If subscription has gone past a subscription cycle into grace period then charge backpay
            if ($newDate->lte($now)) {
                // Calculate the number of subscription cycles that need to be charged
                $quantity = floor($userSubscription->ends_at->diffInMonths($now) / $userSubscription->duration) + 1;
                // Charge for number
            } else {
                // Charge for 1 subscription if new date covers today
                $quantity = 1;
            }
            $price = $userSubscription->price * $quantity;
            $expiresAt = $userSubscription->ends_at
                ->addMonths($quantity * $userSubscription->duration)->format('Y-m-d');
        }
        // Set stupid variables so that a non tax related object can be passed to tax service
        $tax = 0;
        $taxInvoice = null;
        if ($price > 0 && app('globalSettings')->getGlobal('tax_subscription', 'show') == true) {
            $user = User::where('id', $user_id)->with('billingAddress')->with('shippingAddress')->first();
            $companyUser = User::select('pid')->where('id', '=', config('site.apex_user_id'))->first();
            $taxInvoice = (new TaxService)->createSubscriptionTaxInvoice(
                $user->billingAddress,
                $user->shippingAddress,
                $company_address,
                $userSubscription,
                $quantity,
                $companyUser->pid,
                false
            );
            if (isset($taxInvoice->error)) {
                return ['error' => $taxInvoice->error];
            }
            $tax = $taxInvoice->tax;
        }
        return [
            'price' => $userSubscription->price,
            'months' => $userSubscription->duration * $quantity,
            'quantity' => $quantity,
            'subtotal_price' => $price,
            'total_tax' => $tax,
            'tax_invoice' => $taxInvoice,
            'tax_invoice_pid' => isset($taxInvoice->pid) ? $taxInvoice->pid : null,
            'expires_at' => $expiresAt,
            'duration' => $userSubscription->duration
        ];
    }

    public function newUser(User $user, $subscription_id)
    {
        $userSubscription = SubscriptionUser::where('user_id', $user->id)->orderBy('created_at', 'DESC')->first();
        $subscription = Subscription::where('id', $subscription_id)->first();
        $now = Carbon::now();
        if (! $userSubscription) {
            $userSubscription = new SubscriptionUser;
            $userSubscription->subscription_id = $subscription_id;
            $userSubscription->user_id = $user->id;
            $userSubscription->user_pid = $user->pid;
            $userSubscription->auto_renew = $subscription->renewable;
            $userSubscription->ends_at = $now->toDateTimeString();
            $userSubscription->subscription_price = $subscription->plan_price;
        }
        if ($subscription->free_trial_time > 0) {
            $endDate = Carbon::parse($userSubscription->ends_at)
                ->addDays($subscription->free_trial_time)
                ->toDateTimeString();
            $userSubscription->ends_at = $endDate;
            $userSubscription->auto_renew = true;
        } elseif ($subscription->duration == 0) {
            // one-time subscription duration is null so it can't expire
            $userSubscription->ends_at = null;
            $userSubscription->auto_renew = false;
        } else {
            $userSubscription->ends_at = $now->addMonths($subscription->duration);
        }
        $userSubscription->save();
        return $userSubscription;
    }

    public function subscriptionToken($id)
    {
        return CardToken::where('user_id', $id)->where('type', 'subscription')->first();
    }

    public function subscriptionHasUsers($id)
    {
        $hasUser = SubscriptionUser::where('subscription_id', $id)->first();
        if ($hasUser !== null) {
            return true;
        }
        return false;
    }

    public function updateSubscriptionToken($user_id, $data)
    {
        $subscriptionToken = CardToken::where('user_id', $user_id)->where('type', 'subscription')->first();
        $newToken = $this->paymentManager->cardToken($data, $user_id);
        if (!$subscriptionToken) {
            return $this->createSubscriptionCardToken($newToken['data'], $user_id);
        }

        $subscriptionToken->token = $newToken['cardToken'];
        $subscriptionToken->card_digits = $newToken['cardNumber'];
        $subscriptionToken->card_type = $newToken['cardType'];
        $subscriptionToken->expiration    = $newToken['cardExpiration'];
        $subscriptionToken->update();
        //This need some thought on if we keep it.
        $subscriptionUser = SubscriptionUser::where('user_id', $user_id)->first();
        $subscriptionUser['last_fail_attempt'] = Carbon::now();
        $subscriptionUser['card_type'] = $newToken['cardType'];
        $subscriptionUser['card_digits'] = $newToken['cardNumber'];
        if ($newToken['cardExpiration'] > Carbon::now()) {
            $subscriptionUser['card_expired'] = false;
        } else {
            $subscriptionUser = true;
        }
        $subscriptionUser->save();
        return $subscriptionToken;
    }

    public function createSubscriptionReceipt($subscription, $payment, $user_id, $tax_invoice_pid)
    {
        $receipt = SubscriptionReceipt::create([
            'transaction_id' => $payment['transaction']['id'],
            'subscription_id' => $subscription->id,
            'user_id' => $user_id,
            'total_tax' => $payment['transaction']['salesTax'],
            'subtotal_price' => $payment['transaction']['amount'] - $payment['transaction']['salesTax'],
            'total_price' => $payment['transaction']['amount'],
            'title' => $subscription->title,
            'duration' => $subscription->duration,
            'tax_invoice_pid' => $tax_invoice_pid
        ]);
        SubscriptionAttempt::create([
            'user_id' => $user_id,
            'subscription_user_id' => $subscription->subscription_user_id,
            'description' => 'Successful',
            'subscription_receipts_id' => $receipt->id
        ]);
        return $receipt;
    }

    /**
    * Looks like this is used for renewing the subscription
    */
    public function updateUserSubscription($user, $subscription)
    {
        $userSubscription = $user->subscriptions;
        $wasDisabled = $userSubscription->disabled_at !== null;
        if ($subscription->duration == 0) {
            // Duration of 0 means one-time payment, null prevents expire
            $endDate = null;
            $userSubscription->auto_renew = false;
        } else {
            $endDate = Carbon::parse($userSubscription->ends_at)
            ->addMonths($subscription->duration)
            ->toDateTimeString();
        }
        $userSubscription->ends_at = $endDate;
        $userSubscription->fail_description = '';
        $userSubscription->disabled_at = null;
        $userSubscription->save();
        if ($wasDisabled) {
            event(new \App\Events\SubscriptionRenewedEvent($user, $userSubscription));
        }
        return $userSubscription;
    }

    /**
     * To save a new ends_at date.
     */
    public function updateEndsAt($subscription)
    {
        $now = Carbon::now();
        $newDate = Carbon::parse($subscription['billing_date']);
        $updateSubscription = SubscriptionUser::where('id', $subscription['id'])->first();
        $updateSubscription->ends_at = $newDate;
        if (!empty($updateSubscription->disabled_at) && $newDate->gt($now)) {
            $updateSubscription->disabled_at = null;
            event(new \App\Events\SubscriptionRenewedEvent($updateSubscription->user, $updateSubscription));
        } elseif (empty($updateSubscription->disabled_at) && $now->gte($newDate)) {
            $updateSubscription->disabled_at = $now;
            event(new \App\Events\SubscriptionExpiredEvent($updateSubscription));
        }
        $updateSubscription->save();
        return $updateSubscription;
    }

    /**
     * This Method is sending out a email if a card has failed three times.
     * To remind the user that they need to update their info.
     */
    public function expiredSubscriptionBadCard()
    {
        $users = SubscriptionUser::
            where([['auto_renew', 1], ['ends_at', '<', Carbon::now()->subDays(4)], ['card_token.type', 'subscription']])
            ->select('ends_at', 'token', 'subscription_id', 'price', 'subscription_user.user_id', 'subscription_user.subscription_id as id')
            ->join('card_token', 'subscription_user.user_id', 'card_token.user_id')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscription_user.subscription_id')
                    ->where('prices.priceable_type', Subscription::class)
                    ->where('prices.price', '>', 0);
            })
            ->has('user')
            ->with('user')
            ->get();

        return $users;
    }

    public function findSubscriptionsForAutoDisable()
    {
        $now = Carbon::now();
        $gracePeriod = app('globalSettings')->getGlobal('sub_grace_period', 'value');
        // Find non disabled subscriptions so we can disable them
        return SubscriptionUser::whereNull('disabled_at')
            ->where(function ($query) use ($now, $gracePeriod) {
                $query->where('auto_renew', '=', 0)
                    ->orWhereNull('ct.user_id')
                    ->where('ends_at', '<', $now)
                    ->orWhere('ends_at', '<', $now->subDays($gracePeriod));
            })
            ->select('subscription_user.*', 'ct.token', 'price')
            ->leftJoin('card_token AS ct', 'subscription_user.user_id', 'ct.user_id')
            ->join('prices AS p', function ($join) {
                $join->on('p.priceable_id', '=', 'subscription_user.subscription_id')
                    ->where('p.priceable_type', Subscription::class)
                    ->where('p.price', '>', 0);
            })
            ->with('user')->get();
    }

    /**
     * Creates a new card token for subsriptions.
     *
     * @param Array
     * @param Int
     * @return CardToken
     */
    public function createSubscriptionCardToken($cardToken, $user_id)
    {
        $cardNumber = "************" . substr($cardToken['cardNumber'], -4);
        $card = CardToken::create([
           'token' => $cardToken['cardToken'],
           'user_id' => $user_id,
           'type'          => 'subscription',
           'card_type'     => $cardToken['cardType'],
           'card_digits'   => $cardNumber,
           'expiration'    => $cardToken['cardExpiration'],
           'gateway_customer_id' =>$cardToken['gatewayCustomerId']
           ]);
         return $card;
    }

    public function isAffiliate()
    {
        $subscription = Subscription::where('seller_type_id', 1)->get();
        if (count($subscription) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUserSubscriptonOnFail($userId, $description)
    {
        $updateSubscription = SubscriptionUser::where('user_id', $userId);
        $updateSubscription->update(['fail_description' => $description,
            'last_fail_attempt' => Carbon::now()
            ]);
        SubscriptionAttempt::create([
            'user_id' => $userId,
            'subscription_user_id' => $updateSubscription->first()->id,
            'description' => $description
        ]);
    }

    public function expiredSubscriptions()
    {
        $now = Carbon::now();
        $subThree = Carbon::now()->subDays(3);
        $expiredSubscriptions = SubscriptionUser::
            whereBetween('ends_at', [$subThree, $now])
            ->select('ends_at', 'token', 'gateway_customer_id', 'title', 'duration', 'renewable', 'price', 'price as subtotal_price', 'subscription_user.user_id', 'subscription_user.subscription_id as id', 'subscription_user.id as subscription_user_id')
            ->where('auto_renew', 1)
            ->where('card_token.type', 'subscription')
            ->join('card_token', 'subscription_user.user_id', 'card_token.user_id')
            ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscriptions.id')
                    ->where('prices.priceable_type', Subscription::class)
                    ->where('prices.price', '>', 0);
            })
            ->has('user')
            ->with('user')
            ->get();
        return $expiredSubscriptions;
    }

    public function expiredFreeSubscriptions()
    {
        $expiredSubscriptions = SubscriptionUser::
            select('ends_at', 'title', 'duration', 'renewable', 'price', 'subscription_user.user_id')
            ->where('auto_renew', 1)
            ->where('ends_at', '<', Carbon::now())
            ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscriptions.id')
                    ->where('prices.priceable_type', Subscription::class)
                    ->where('prices.price', '=', 0);
            })
            ->has('user')
            ->with('user')
            ->get();
        return $expiredSubscriptions;
    }

    public function renewUserForAdmin($user)
    {
        $userSubscription = SubscriptionUser::where('user_id', $user['user_id'])->first();
        if (!$userSubscription) {
            return ['error' => true, 'message' => 'No user subscription found.'];
        }
        $userSubscription->ends_at = $user['ends_at'];
        $userSubscription->save();
        return $userSubscription;
    }

    public function nearExpiredSubscriptons()
    {
        $subscriptions = SubscriptionUser::
        whereBetween('ends_at', [Carbon::now()->addDays(7), Carbon::now()->addDays(8)])
        ->where('auto_renew', 1)
        ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
        ->join('prices', function ($join) {
            $join->on('prices.priceable_id', '=', 'subscriptions.id')
                ->where('prices.priceable_type', Subscription::class)
                ->where('prices.price', '>', 0);
        })
        ->has('user')
        ->with('user')
        ->select('ends_at', 'price', 'title', 'subscription_user.user_id')
        ->get();
        return $subscriptions;
    }

    public function subscriptonsWithOutCard()
    {
        $subscriptions = SubscriptionUser::
            where('auto_renew', 1)
            ->join('subscriptions', 'subscriptions.id', 'subscription_user.subscription_id')
            ->join('prices', function ($join) {
                $join->on('prices.priceable_id', '=', 'subscriptions.id')
                    ->where('prices.priceable_type', Subscription::class)
                    ->where('prices.price', '>', 0);
            })
            ->doesntHave('cardToken')
            ->has('user')
            ->with('user')
            ->select('subscription_user.user_id', 'subscription_user.*')
            ->get();
        return $subscriptions;
    }
    public function reportTransactions($request)
    {
        $timezone = $this->getTimeZone();
        $startDate = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $receipt = SubscriptionReceipt::whereBetween('created_at', [$startDate, $endDate])->get();
        return $receipt;
    }

    public function autoRenewCount()
    {
        $subscriptionsRenew = SubscriptionUser::where('auto_renew', 1)->get();
        return count($subscriptionsRenew);
    }

    public function userReceipt($user_id, $request)
    {
        return SubscriptionReceipt::where('user_id', $user_id)
                                ->paginate($request['per_page']);
    }

    public function getAllReceipt($request)
    {
        $timezone = $this->getTimeZone();
        $startDate = Carbon::parse($request['start_date'], $timezone)
                            ->startOfDay()
                            ->setTimezone('UTC')
                            ->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($request['end_date'], $timezone)
                            ->endOfDay()
                            ->setTimezone('UTC')
                            ->format('Y-m-d H:i:s');

        $receipt = SubscriptionReceipt::whereBetween('subscription_receipts.created_at', [$startDate, $endDate])
                            ->join('users', 'user_id', 'users.id')
                            ->select('subscription_receipts.*', 'users.first_name', 'users.last_name');

        if (! empty($request['search_term'])) {
            $receipt->where(function ($query) use ($request) {
                $query->where('users.id', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhere('users.first_name', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhere('users.last_name', 'LIKE', '%' . $request['search_term'] . '%')
                ->orWhereRaw("(CONCAT(first_name, ' ', last_name) LIKE '%".$request['search_term'] ."%')")
                ->orWhere('subscription_receipts.title', 'LIKE', '%' . $request['search_term'] . '%');
            });
        }
        if (isset($request['column'])) {
            $receipt->orderBy($request['column'], $request['order']);
        }
        if (isset($request['per_page'])) {
            return $receipt->paginate($request['per_page']);
        } else {
            return $receipt->get();
        }
    }

    private function getTimeZone()
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $user_id = config('site.apex_user_id');
        } else {
            $user_id = auth()->user()->id;
        }
        $timeZone = UserSetting::where('user_id', $user_id)->first();
        if ($timeZone == null) {
            $timeZone = 'UTC';
        } else {
            $timeZone = $timeZone->timezone;
        }

        return $timeZone;
    }
}
