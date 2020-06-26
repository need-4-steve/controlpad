<?php
namespace address;

use \ApiTester;
use \App\Models\Address;
use \Step\Api\UserAuth;

class AddressCest
{
    public function _before(UserAuth $I)
    {
        $this->address = Address::first();
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryPostCreate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Post an new Address');
        $address = [
                'name' => 'newName',
                'address_1' => '123',
                'address_2' => 'apt 13',
                'city' => 'provo',
                'state' => 'UT',
                'addressable_id' => config('site.apex_user_id'),
                'zip' => 84601,
                'addressable_type' => 'App\Models\User',
                'label' => 'Shipping',
        ];
        $I->sendAjaxRequest('POST', '/api/v1/address/create-or-update', $address);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($address);
    }
    public function tryPostCreateAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Post an new Address As Rep');
        $address = [
                'name' => 'testUser',
                'address_1' => '123',
                'address_2' => 'apt 13',
                'city' => 'provo',
                'state' => 'UT',
                'addressable_id' => REP_ID,
                'zip' => 84601,
                'addressable_type' => 'App\Models\User',
                'label' => 'Shipping',
        ];
        $I->sendAjaxRequest('POST', '/api/v1/address/create-or-update', $address);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryGetShow(UserAuth $I)
    {
         $I->loginAsAdmin();
         $I->wantTo('Get an adrress to show.');
         //uid id not in the db
         $I->sendAjaxRequest('GET', '/api/v1/address/show/', ['label' => 'Shipping',
                            'addressable_type' => 'App\Models\User']);
         $I->seeResponseCodeIs(200);
    }
    public function tryGetShowAsRep(UserAuth $I)
    {
         $I->loginAsRep();
         $I->wantTo('Get an adrress to show As Rep.');
         //uid id not in the db
         $I->sendAjaxRequest('GET', '/api/v1/address/show/', ['label' => 'Shipping',
                            'addressable_type' => 'App\Models\User']);
         $I->seeResponseCodeIs(200);
    }
}
