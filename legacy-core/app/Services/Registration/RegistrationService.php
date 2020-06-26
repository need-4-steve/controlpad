<?php

namespace App\Services\Registration;

use App\Models\Cart;
use App\Services\PayMan\PayManService;
use App\Services\Tax\TaxService;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Http\Controllers\Api\V1\OrderController;
use App\Exceptions\APIResponseException;
use App\Models\Order;
use App\Models\Setting;
use App\Models\StoreSetting;
use App\Models\Subscription;
use App\Models\SubscriptionUser;
use App\Models\SubscriptionReceipt;
use App\Models\UserSetting;
use App\Models\CardToken;
use App\Models\TaxInvoice;
use App\Models\User;
use App\Services\Authentication\JWTAuthService;

class RegistrationService
{
    protected $authRepo;
    protected $paymentManager;
    protected $cartRepo;
    protected $orderRepo;
    protected $orderController;
    protected $subscriptionRepo;
    protected $taxService;

    public function __construct(
        PayManService $payMan,
        UserRepository $userRepo,
        CartRepository $cartRepo,
        OrderRepository $orderRepo,
        SubscriptionRepository $subscriptionRepo,
        OrderController $orderController,
        TaxService $taxService
    ) {
        $this->paymentManager = $payMan;
        $this->userRepo = $userRepo;
        $this->orderRepo = $orderRepo;
        $this->cartRepo = $cartRepo;
        $this->orderController = $orderController;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->taxService = $taxService;
    }

    /**
     * Creates a new user, processes subscription cost
     *
     * @param Array
     * @param Subscription
     * @return Subscription
     */
    public function processNewSubscription($request, $subscription, ?Order $order = null)
    {
        if ($subscription->price->price > 0) {
            if ($subscription->free_trial_time <= 0) {
                $transaction = $this->paymentManager->makePayment(
                    'sub',
                    null,
                    1,
                    'company',
                    $request['payment'],
                    $subscription->price->price,
                    $request['total_tax'],
                    0.00,
                    'Subscription',
                    $request['addresses']['billing']
                );
                if ($transaction['resultCode'] != 1) {
                    return $transaction;
                }
            }
        }

        // create user
        $user = $this->userRepo->createRep($request, $order);
        if (isset($request['payment']) && $request['payment']['type'] == 'card' && $subscription->price->price > 0) {
            // create card token for auto subscription renewal
            try {
                $cardToken = $this->paymentManager->cardTokenV2($request['payment']['card'], $request['addresses']['billing'], $user->id);
            } catch (APIResponseException $e) {
                logger()->info($e);
            }
            if (isset($cardToken['data'])) {
                $this->subscriptionRepo->createSubscriptionCardToken($cardToken['data'], $user->id);
            }
        }

        $userSubscription = $this->subscriptionRepo->newUser($user, $subscription->id);
        event(new \App\Events\SubscriptionCreatedEvent($user, $userSubscription));

        if ($subscription->free_trial_time <= 0 && $subscription->price->price > 0) {
            $taxInvoicePid = isset($request['tax_invoice_pid']) ? $request['tax_invoice_pid'] : null;
            $subscription->subscription_user_id = $userSubscription->id;
            $receipt = $this->subscriptionRepo->createSubscriptionReceipt(
                $subscription,
                ['transaction' => $transaction],
                $user->id,
                $taxInvoicePid
            );
            if ($taxInvoicePid != null) {
                $this->taxService->queueTaxInvoiceCommit($taxInvoicePid, 'Sub:'.$receipt->id);
            }
        }
        // log the new user in
        $jwtToken = JWTAuthService::createToken($user->makeVisible(['remember_token']));
        return [
            'userSubscription' => $userSubscription,
            'jwtToken' => $jwtToken
        ];
    }

    public function processStarterKit($orderRequest, $starterKitId)
    {
        session()->forget(['cart', 'store_owner']);
        $cart = new Cart;
        $cart->type = 'wholesale';
        $cart->save();
        $cart = $this->cartRepo->putBundle($starterKitId, $cart, 1, true);
        $cart = $this->cartRepo->updateTotals($cart, null, $cart->type, app('globalSettings')->getGlobal('registration_shipping', 'value'));
        session()->put('cart', $cart);
        // create order, pass true to indicate that we have a starter kit to create
        $orders = $this->orderController->create($orderRequest, true)->getData(true);
        // It needs to be done this way untill registration is refactored using the new checkout process
        if (!isset($orders[0]['id'])) {
            return ['success' => false, 'description' => $orders];
        }
        $order = $this->orderRepo->find($orders[0]['id']);
        $order->type_id = 1; // corp to rep
        $order->save();
        return $order;
    }

    public function createPaymentAccount($user, $ppa)
    {
        $checkUser = $this->checkUserAccount(auth()->id());
        if ($checkUser) {
            return [
               'error' => true,
               'status' => 400,
               'body' => 'You are already registered to accept payments.'
            ];
        }
        $MCCode = Setting::where('key', 'merchant_category_code')->first();
        $MCCode->value = json_decode($MCCode->value)->value;
        $ppa['merchantCategoryCode'] = $MCCode->value;
        $paymentManagerAccount = $this->paymentManager->subAccounts($user, $ppa);
        if (!$paymentManagerAccount['error']) {
            // TODO: this should have been a repository method
            $user_setting = UserSetting::where('user_id', auth()->id())->first();
            if ($user_setting === null) {
                logger()->error('User settings where not found for the authorized user on new splash registration.' . auth()->id());
                return [
                    'error' => true,
                    'status' => 500,
                    'body' => 'There was an internal error with settings. Please contact support.'
                ];
            }
            $user_setting->update(['payment_account' => 1]);
            cache()->forget('user-settings-'.auth()->id());
        }
        return $paymentManagerAccount;
    }

    public function checkUserAccount($user_id)
    {
        $checkUser = $this->paymentManager->subAccountExists($user_id);
        if ($checkUser) {
            // if they have an account mark it as a user setting.
            // TODO: needs to use a repoistory method here
            $user_setting = UserSetting::where('user_id', $user_id)->first();
            $user_setting->update(['payment_account' => 1]);
            return $checkUser;
        }
        return false;
    }

    /**
     * Create subscriptions for users that signed up,
     * but somehow the system failed to finish the process.
     * An example of this if the database locks up in the middle of signup.
     */
    public function fixRepsWithoutSubscription()
    {
        $this->storeSettingsRepo = new StoreSettingRepository;
        $this->userSettingsRepo = new UserSettingsRepository($this->subscriptionRepo);
        // fix reps that don't have a subscription
        $usersWithoutSubscriptions = User::doesntHave('subscriptions')->where('role_id', 5)->get();
        $subscriptions = Subscription::select('id')->where('on_sign_up', true)->get();
        if (isset($subscriptions) && count($subscriptions) === 1) {
            $subscription = $subscriptions->first();
        } else {
            $usersWithoutSubscriptions = ['error' => 'There is more then one possible subscription on signup. Cannot automatically associate a subscription to a user.'];
        }
        if (!isset($usersWithoutSubscriptions['error'])) {
            foreach ($usersWithoutSubscriptions as $user) {
                $user->fingerprint = 'Rep created w/o a subscription';
                $this->subscriptionRepo->newUser($user, $subscription->id);
                logger()->warning($user->fingerprint, $user->toArray());
            }
        }

        // fix reps that don't have settings
        $usersWithoutStoreSettings = User::doesntHave('storeSettings')->where('role_id', 5)->get();
        foreach ($usersWithoutStoreSettings as $user) {
            $this->storeSettingsRepo->createSettings($user->id, $user->first_name.' '.$user->last_name);
            $user->fingerprint = 'Rep created w/o store settings';
            logger()->warning($user->fingerprint, $user->toArray());
        }

        $usersWithoutSettings = User::doesntHave('settings')->where('role_id', 5)->get();
        foreach ($usersWithoutSettings as $user) {
            $this->userSettingsRepo->newUser($user->id, $user->pid);
            $user->fingerprint = 'Rep created w/o user settings';
            logger()->warning($user->fingerprint, $user->toArray());
        }
        return [
            "Reps that didn't have a subscription" => $usersWithoutSubscriptions,
            "Reps that didn't have store settings" => $usersWithoutStoreSettings,
            "Reps that didn't have user settings" => $usersWithoutSettings
        ];
    }
}
