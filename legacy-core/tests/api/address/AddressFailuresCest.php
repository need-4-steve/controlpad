<?php
namespace address;

use \ApiTester;
use \App\Models\Address;
use \Step\Api\UserAuth;

class AddressFailuresCest
{
    public function _before(UserAuth $I)
    {
        $this->address = Address::first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests

    public function tryPostCreate(UserAuth $I)
    {
        $I->wantTo('Post an new Address');
        $I->sendAjaxRequest('POST', '/api/v1/address/create', [
                'address_1' => '123',
                'address_2' => 'apt 13',
                'city' => 'provo',
                'state' => 'UT',
                'addressable_id' => 10,
                'zip' => 84601,
                'addressable_type' => 'App\Models\Orders',
                'label' => 'shipping',
                'title' => 'test',
                'description' => 'test description',
                'disabled' => 0
            ]);
        $I->seeResponseCodeIs(401);
    }

    public function tryGetShow(UserAuth $I)
    {
         $I->wantTo('Get an adrress to show.');
         //uid id not in the db
         $I->sendAjaxRequest('GET', '/api/v1/address/show/');
         $I->seeResponseCodeIs(401);
    }
}
