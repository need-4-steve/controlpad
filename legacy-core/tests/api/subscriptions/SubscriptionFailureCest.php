<?php
namespace subscripitons;

use \ApiTester;
use \App\Models\Subscription;
use \App\Models\SubscriptionUser;
use \App\Models\User;
use \Step\Api\UserAuth;

class SubscriptionFailureCest
{
    private $subscription;
    private $subscriptionUser;

    public function _before(ApiTester $I)
    {
        $this->subscription = Subscription::with('price')->first();
        $this->subscriptionUser = SubscriptionUser::where('user_id', 106)->with('user', 'subscription.price')->first();
    }

    public function _after(ApiTester $I)
    {
    }

    public function tryToTestGetAllSubsciptionsNotLogged(UserAuth $I)
    {
        $I->wantTo('I want to fail to get all subscriptions when not logged in');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/all-subscriptions');
        $I->seeResponseCodeIs(401);
    }

    public function tryToTestGetUserSubscriptionsAsRep(UserAuth $I)
    {
        $I->wantTo('Get All User Subscriptions as rep');
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/user-subscriptions');
        $I->seeResponseCodeIs(403);
    }

    public function tryToTestPostCreate(UserAuth $I)
    {
        $I->wantTo('Create New a Subscription As Rep');
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', '/api/v1/subscriptions/create', [
            'title'           => 'Quarterly Subscription',
            'duration'        => 90,
            'renewable'       => true,
            'price'           => 49.99,
            'free_trial_time' => 0,
        ]);
        $I->seeResponseCodeIs(403);
    }

    public function tryToTestPostCreateNotLogged(UserAuth $I)
    {
        $I->wantTo('Create New a Subscription when not logged in');
        $I->sendAjaxRequest('POST', '/api/v1/subscriptions/create', [
            'title'           => 'Quarterly Subscription',
            'duration'        => 90,
            'renewable'       => true,
            'price'           => 49.99,
            'free_trial_time' => 0,
        ]);
        $I->seeResponseCodeIs(401);
    }

    public function tryToTestGetShowNotLogged(UserAuth $I)
    {
        $I->wantTo('Get a subscription NOT logged in');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/show');
        $I->seeResponseCodeIs(401);
    }

    public function tryToTestPutEditByRep(UserAuth $I)
    {
        $I->wantTo('Edit A Subscription as Rep');
        $I->loginAsRep();
        $I->sendAjaxRequest('PUT', '/api/v1/subscriptions/edit/'.$this->subscription->id, [
            'title'     => 'Quarterly Subscription',
            'duration'  => $this->subscription->duration,
            'renewable' => $this->subscription->renewable,
            'price'     => [$this->subscription->price],
        ]);
        $I->seeResponseCodeIs(403);
    }

    public function tryToTestPutEditNotLogged(UserAuth $I)
    {
        $I->wantTo('Edit A Subscription NOT login');
        $I->sendAjaxRequest('PUT', '/api/v1/subscriptions/edit/'.$this->subscription->id, [
            'title'     => 'Quarterly Subscription',
            'duration'  => $this->subscription->duration,
            'renewable' => $this->subscription->renewable,
            'price'     => [$this->subscription->price],
        ]);
        $I->seeResponseCodeIs(401);
    }

    public function tryToTestGetShowPlan(UserAuth $I)
    {
        $I->wantTo('Get A Subscription Plan NOT logged in');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/show-plan/'.$this->subscription->id);
        $I->seeResponseCodeIs(401);
    }
}
