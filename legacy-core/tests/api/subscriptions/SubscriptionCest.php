<?php
namespace subscripitons;

use \ApiTester;
use \App\Models\Subscription;
use \App\Models\SubscriptionUser;
use \App\Models\User;
use \Step\Api\UserAuth;

class SubscriptionCest
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

    public function tryToTestGetAllSubsciptions(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Get All Subscriptions as admin');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/all-subscriptions');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [
                0 => [
                    'id' => $this->subscription->id,
                    'price' => [
                        'price' => $this->subscription->price->price
                    ]
                ]
            ]

        ]);
    }

    public function tryToTestGetAllSubsciptionsAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Get All Subscriptions as rep');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/all-subscriptions');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $this->subscription->id,
            'price' => [
                'price' => $this->subscription->price->price
            ]
        ]);
    }

    public function tryToTestGetUserSubscriptions(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Get All User Subscriptions as admin');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/user-subscriptions?per_page=15');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'data' => [
                0 => [
                    'id' => $this->subscriptionUser->id,
                    'user_id' => $this->subscriptionUser->user->id,
                    'price' => $this->subscriptionUser->Subscription->price->price
                ]
            ]
        ]);
    }

    public function tryToTestPostCreate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Admin to Create New a Subscription');
        $inputs = [
            'title' => 'Quarterly Subscription',
            'duration' => 90,
            'renewable' => true,
            'price' => [
                'price' => 49.99,
            ],
            'free_trial_time' => 0,
            'on_sign_up' => 1,
            'seller_type_id' => 2,
            'tax_class' => '00000000'
        ];
        $I->sendAjaxRequest('Post', '/api/v1/subscriptions/create', $inputs);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
            'statusCode' => 200,
            'message' => 'Subscription created.',
            'data' => [
                'title' => 'Quarterly Subscription',
                'duration' => 90,
                'renewable' => true,
            ]
        ]);
        $I->seeRecord('subscriptions', [
            'title' => 'Quarterly Subscription',
            'duration' => 90,
            'renewable' => true
        ]);
        $subscription = $I->grabRecord('subscriptions', [
            'title' => 'Quarterly Subscription',
            'duration' => 90,
            'renewable' => true
        ]);
        $I->seeRecord('prices', [
            'priceable_id' => $subscription['id'],
            'priceable_type' => 'App\\Models\\Subscription',
            'price' => $inputs['price']
        ]);
    }

    public function tryToTestGetShow(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Get a subscription for logged in rep');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/show');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'user' => [
                'id' => $this->subscriptionUser->user_id
            ],
            'lastSubscription' => [
                'user_id' => $this->subscriptionUser->user_id,
            ],
            'price' => $this->subscription->price->price

        ]);
    }

    public function tryToTestPutEdit(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Edit A Subscription as Admin');
        $inputs = [
            'title' => 'Quarterly Subscription',
            'duration' => $this->subscription->duration,
            'renewable' => $this->subscription->renewable,
            'free_trial_time' => 0,
            'price' => array($this->subscription->price),
            'on_sign_up' => 1,
            'seller_type_id' => 2,
            'tax_class' => '00000111'
        ];
        $I->sendAjaxRequest('PUT', '/api/v1/subscriptions/edit/'.$this->subscription->id, $inputs);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
            'statusCode' => 200,
            'message' => 'Subscription updated.',
            'data' => [
                'title' => 'Quarterly Subscription',
                'duration' => $this->subscription->duration,
                'renewable' => $this->subscription->renewable
            ]
        ]);
        $I->seeRecord('subscriptions', [
            'title' => 'Quarterly Subscription',
            'duration' => $this->subscription->duration,
            'renewable' => $this->subscription->renewable
        ]);
        $subscription = $I->grabRecord('subscriptions', [
            'id' => $this->subscription->id,
            'title' => 'Quarterly Subscription',
            'duration' => $this->subscription->duration,
            'renewable' => $this->subscription->renewable
        ]);
        $I->seeRecord('prices', [
            'priceable_id' => $subscription['id'],
            'priceable_type' => 'App\\Models\\Subscription',
            'price' => $this->subscription->price->price
        ]);
    }

    public function tryToTestGetShowPlan(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Get A Subscription Plan as Admin');
        $I->sendAjaxRequest('GET', '/api/v1/subscriptions/show-plan/'.$this->subscription->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'id' => $this->subscription->id,
            'duration' => $this->subscription->duration,
            'renewable' => $this->subscription->renewable,
            'price' => [
                'price' => $this->subscription->price->price
            ]
        ]);
    }
}
