<?php
namespace registrations;

use \ApiTester;
use \App\Models\User;
use \Step\Api\UserAuth;
use \Carbon\Carbon;
use App\Models\Bundle;
use App\Models\Address;
use DB;

class RegistrationsCest
{
    private $user;

    private $newId;

    public function _before(UserAuth $I)
    {
        $address = factory(Address::class, 1)
            ->make()
            ->toArray();

        $this->request = [
            "total_tax" => 0,
            "tax_invoice_pid" => null,
            "user" => [
                "first_name" => "test",
                "last_name" => "test",
                "email" => uniqid()."@example.com",
                "password" => "password2",
                "public_id" => uniqid()
            ],
            "subscription_id" => 1,
            "agree" => true,
            "payment" => [
                "name" => "test test",
                "card_number" => 4111111111111111,
                "security" => 555,
                "month" => 10,
                "year" => 2025
            ],
            "addresses" => [
                "shipping" => $address,
                "billing" => $address
            ],
            "subscription_bill" => true
        ];
        DB::table('settings')->where('key', 'reseller_payment_option')->update([
            'value' => '{"value": "", "show": false}',
        ]);
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryRegisterWithStarterKit(UserAuth $I)
    {
        $bundle = Bundle::first();
        $bundle->update(['starter_kit' => true]);
        $I->sendAjaxRequest('GET', "/api/v1/starter-kits");
        $starterKit = json_decode($I->grabResponse(), true);
        $this->request['starter_kit_id'] = $starterKit[0]['id'];
        $I->sendAjaxRequest('POST', '/api/v1/register', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('users', ['email' => $this->request['user']['email'], 'role_id' => 5, 'public_id' => $this->request['user']['public_id']]);
        $user = User::where('email', $this->request['user']['email'])->first();
        $I->seeRecord('subscription_user', ['user_id' => $user->id, 'subscription_id' => $this->request['subscription_id']]);
        $I->seeRecord('subscription_receipts', ['user_id' => $user->id, 'subscription_id' => $this->request['subscription_id']]);
        $I->seeRecord('orders', ['customer_id' => $user->id]);
        $I->seeRecord('orderlines', ['bundle_name' => $bundle->name, 'type' => 'Bundle', 'quantity' => 1]);
    }

    public function tryRegisterWithoutStarterKit(UserAuth $I)
    {
        $I->sendAjaxRequest('POST', '/api/v1/register', $this->request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('users', ['email' => $this->request['user']['email'], 'role_id' => 5, 'public_id' => $this->request['user']['public_id']]);
        $user = User::where('email', $this->request['user']['email'])->first();
        $I->seeRecord('subscription_user', ['user_id' => $user->id, 'subscription_id' => $this->request['subscription_id']]);
        $I->seeRecord('subscription_receipts', ['user_id' => $user->id, 'subscription_id' => $this->request['subscription_id']]);
    }
}
