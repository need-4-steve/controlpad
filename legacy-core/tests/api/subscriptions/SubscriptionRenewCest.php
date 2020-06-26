<?php
namespace subscripitons;

use \Step\Api\UserAuth;
use App\Services\Subscription\SubscriptionService;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Services\PayMan\PayManService;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SubscriptionUser;
use App\Models\Subscription;
use App\Models\Price;
use DB;
use CPCommon\Pid\Pid;

class SubscriptionRenewCest
{
    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService(new AuthRepository, new SubscriptionRepository(new PayManService));
    }

    public function _before(UserAuth $I)
    {
        $this->userId = $I->haveRecord('users', [
            'first_name' => 'Controlpad',
            'last_name' => 'Test',
            'email' => 'controlpad@example.com',
            'role_id' => '5',
            'seller_type_id' => '2'
        ]);

        $this->subscriptionId = $I->haveRecord('subscriptions', [
            'duration' => 1,
            'renewable' => true
        ]);

        $this->subscriptionUserId = $I->haveRecord('subscription_user', [
            'user_id' => $this->userId,
            'auto_renew' => true,
            'ends_at' => Carbon::now()->subDays(3),
            'subscription_id' => $this->subscriptionId
        ]);
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryRenewSubscriptions(UserAuth $I)
    {
        $subscriptionPriceId = $I->haveRecord('prices', [
            'priceable_id' => $this->subscriptionId,
            'priceable_type' => Subscription::class,
            'price' => 49.49
        ]);

        $cardTokenId = $I->haveRecord('card_token', [
            'user_id' => $this->userId,
            'card_type' => 'V',
            'expiration' => '1299',
            'token' => 'abcdefghijklmnopq',
            'type' => 'subscription'
        ]);

        $subscriptionUserBefore = SubscriptionUser::find($this->subscriptionUserId);
        $this->subscriptionService->renewExpiredSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($this->subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->addMonth()->toDateString() === $subscriptionUserAfter->ends_at->toDateString());

        $I->seeRecord('subscription_receipts', [
            'subscription_id' => $this->subscriptionId,
            'user_id' => $this->userId,
            'subtotal_price' => 49.49
        ]);
    }

    public function tryRenewFreeSubscriptions(UserAuth $I)
    {
        $subscriptionPriceId = $I->haveRecord('prices', [
            'priceable_id' => $this->subscriptionId,
            'priceable_type' => Subscription::class,
            'price' => 0
        ]);

        $subscriptionUserBefore = SubscriptionUser::find($this->subscriptionUserId);
        $this->subscriptionService->renewExpiredFreeSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($this->subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->addMonth()->toDateString() === $subscriptionUserAfter->ends_at->toDateString());

        $I->dontSeeRecord('subscription_receipts', [
            'subscription_id' => $this->subscriptionId,
            'user_id' => $this->userId,
            'subtotal_price' => 0.00
        ]);
    }

    public function tryRenewOldFreeSubscriptions(UserAuth $I)
    {
        $subscriptionPriceId = $I->haveRecord('prices', [
            'priceable_id' => $this->subscriptionId,
            'priceable_type' => Subscription::class,
            'price' => 0
        ]);

        $subscriptionUserBefore = SubscriptionUser::find($this->subscriptionUserId);
        $subscriptionUserBefore->ends_at = Carbon::now()->subYear();
        $subscriptionUserBefore->save();
        $this->subscriptionService->renewExpiredFreeSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($this->subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->addMonth()->toDateString() === $subscriptionUserAfter->ends_at->toDateString());

        $I->dontSeeRecord('subscription_receipts', [
            'subscription_id' => $this->subscriptionId,
            'user_id' => $this->userId,
            'subtotal_price' => 0.00
        ]);
    }

    public function tryRenewSubscriptionsWithUserHavingNoSubscription(UserAuth $I)
    {
        // delete the previous user's subscription
        DB::table('subscription_user')->delete($this->subscriptionUserId);

        $subscriptionPriceId = $I->haveRecord('prices', [
            'priceable_id' => $this->subscriptionId,
            'priceable_type' => Subscription::class,
            'price' => 49.49
        ]);

        // add another user after to make sure it doesn't fail with the previous user
        $userId = $I->haveRecord('users', [
            'first_name' => 'Controlpad',
            'last_name' => 'Test2',
            'pid' => Pid::create(),
            'email' => 'controlpad1@example.com',
            'role_id' => '5',
            'seller_type_id' => '2'
        ]);

        $subscriptionUserId = $I->haveRecord('subscription_user', [
            'user_id' => $userId,
            'auto_renew' => true,
            'ends_at' => Carbon::now()->subDays(3),
            'subscription_id' => $this->subscriptionId
        ]);

        $cardTokenId = $I->haveRecord('card_token', [
            'user_id' => $userId,
            'card_type' => 'V',
            'expiration' => '1299',
            'token' => 'abcdefghijklmnopq',
            'type' => 'subscription'
        ]);

        $subscriptionUserBefore = SubscriptionUser::find($subscriptionUserId);
        $this->subscriptionService->renewExpiredSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->addMonth()->toDateString() === $subscriptionUserAfter->ends_at->toDateString());

        $I->seeRecord('subscription_receipts', [
            'subscription_id' => $this->subscriptionId,
            'user_id' => $userId,
            'subtotal_price' => 49.49
        ]);
    }
}
