<?php

namespace registrations;

use App\Models\Address;
use App\Models\Blacklist;

use \ApiTester;

class RegistrationsFailCest
{
    public $request;

    public function _before(ApiTester $I)
    {
        $address = factory(Address::class, 1)->make()->toArray();

        $this->request = [
            "user" => [
                "first_name" => "test",
                "last_name"  => "test",
                "email"      => uniqid()."@example.com",
                "password"   => "password2",
                "public_id"  => null,
            ],
            "subscription_id" => 1,
            "agree"           => true,
            "payment"         => [
                "name"        => "test test",
                "card_number" => 4111111111111111,
                "security"    => 555,
                "month"       => 10,
                "year"        => 2025,
            ],
            "addresses" => [
                "shipping" => $address,
                "billing"  => $address,
            ]
        ];
    }

    public function _after(ApiTester $I)
    {
        //
    }

    // tests
    public function tryRegisteringBlacklistedPublicId(ApiTester $I)
    {
        $I->wantTo("try to register a user using a blacklisted public_id");

        $blacklisted = Blacklist::all()->random();
        $this->request['user']['public_id'] = $blacklisted->name;

        $I->sendAjaxRequest('POST', '/api/v1/register', $this->request);
        $I->seeResponseCodeIs(422);
    }


    public function tryRegisteringWithoutPublicId(ApiTester $I)
    {
        $I->wantTo("try to register a user without public_id");

        $I->sendAjaxRequest('POST', '/api/v1/register', $this->request);
        $I->seeResponseCodeIs(422);
    }
}
