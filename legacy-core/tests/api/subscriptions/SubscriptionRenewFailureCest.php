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

class SubscriptionRenewFailureCest
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
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryRenewSubscriptionsOutOfTimeFrame(UserAuth $I)
    {
        $subscriptionUserId = $I->haveRecord('subscription_user', [
            'user_id' => $this->userId,
            'auto_renew' => true,
            'ends_at' => Carbon::now()->subDays(4),
            'subscription_id' => $this->subscriptionId
        ]);

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

        $subscriptionUserBefore = SubscriptionUser::find($subscriptionUserId);
        $this->subscriptionService->renewExpiredSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->toDateString() === $subscriptionUserAfter->ends_at->toDateString());
    }

    public function tryRenewSubscriptionsWithoutCardOnFile(UserAuth $I)
    {
        $subscriptionUserId = $I->haveRecord('subscription_user', [
            'user_id' => $this->userId,
            'auto_renew' => true,
            'ends_at' => Carbon::now()->subDays(3),
            'subscription_id' => $this->subscriptionId
        ]);

        $subscriptionPriceId = $I->haveRecord('prices', [
            'priceable_id' => $this->subscriptionId,
            'priceable_type' => Subscription::class,
            'price' => 49.49
        ]);

        $subscriptionUserBefore = SubscriptionUser::find($subscriptionUserId);
        $this->subscriptionService->renewExpiredSubscriptions();
        $subscriptionUserAfter = SubscriptionUser::find($subscriptionUserId);

        // Make sure a month was added to the subscription
        $I->assertTrue($subscriptionUserBefore->ends_at->toDateString() === $subscriptionUserAfter->ends_at->toDateString());
    }
}
