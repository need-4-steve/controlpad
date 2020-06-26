<?php

use App\User;
use App\Address;
use App\Plan;
use Carbon\Carbon;

class UserTest extends TestCase
{
    protected static $userStructure = [
        'id',
        'pid',
        'first_name',
        'last_name',
        'sponsor_id',
        'public_id',
        'email',
        'created_at',
        'role_id',
        'role',
    ];

    private static function getAddressStructure($type)
    {
        return [
            $type.'_address' => [
                'name',
                'line_1',
                'line_2',
                'city',
                'state',
                'zip',
                'label',
            ]
        ];
    }

    private function getJsonStructure(array $addressTypes = [])
    {
        $structure = $this::$userStructure;
        foreach ($addressTypes as $type) {
            $structure = array_merge($structure, $this::getAddressStructure($type));
        }
        return $structure;
    }

    public function testFindCustomer()
    {
        $user = $this->createUser('Customer');
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testFindAdmin()
    {
        $user = $this->createUser('Admin');
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testFindSuperAdmin()
    {
        $user = $this->createUser('Admin');
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid, ['addresses' => true]);
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testFindRepByPid()
    {
        $user = $this->createUser('Rep');
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping', 'business']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testFindRepById()
    {
        $user = $this->createUser('Rep');
        $response = $this->basicRequest('GET', '/api/v0/users/id/'.$user->id, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping', 'business']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testFindRepByEmail()
    {
        $user = $this->createUser('Rep');
        $response = $this->basicRequest('GET', '/api/v0/users/email/'.$user->email, ['addresses' => true]);
        $response->seeJsonStructure($this->getJsonStructure(['billing', 'shipping', 'business']));
        $response->seeJson([
            'id' => $user->id,
            'pid' => $user->pid,
            'email' => $user->email
        ]);
    }

    public function testIndex()
    {
        $users = [
            $this->createUser('Superadmin'),
            $this->createUser('Admin'),
            $this->createUser('Rep'),
            $this->createUser('Customer'),
        ];
        $response = $this->basicRequest('GET', '/api/v0/users/', ['addresses' => 1, 'sort_by' => '-created_at']);
        $response->assertResponseStatus(200);
        $response->seeJsonStructure(['data' =>
            [
                '*' => $this->getJsonStructure()
            ]
        ]);
        foreach ($users as $user) {
            $response->seeJson([
                'id' => $user->id,
                'pid' => $user->pid,
                'email' => $user->email
            ]);
        }
    }

    public function testCreateCustomer()
    {
        $plan = factory(Plan::class)->create();
        $user = factory(User::class)->make([
            'role_id' => 3,
            'public_id' => null,
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $response = $this->basicRequest('POST', '/api/v0/users/', $user->toArray());
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($user->toArray(), [
            'role_id',
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));
        $customer = json_decode($response->response->getContent());
        $this->notSeeInDatabase('user_setting', [
            'user_id' => $customer->id,
            'user_pid' => $customer->pid,
        ]);
        $this->notSeeInDatabase('store_settings', [
            'user_id' => $customer->id,
            'user_pid' => $customer->pid,
        ]);
        $this->notSeeInDatabase('subscription_user', [
            'user_id' => $customer->id,
            'user_pid' => $customer->pid,
        ]);
        $this->seeAddressInDatabase($user->billing_address, 'Billing');
        $this->seeAddressInDatabase($user->shipping_address, 'Shipping');
    }

    public function testCreateRep()
    {
        $plan = factory(Plan::class)->create();
        $user = factory(User::class)->make([
            'role_id' => 5,
            'public_id' => uniqid(),
            'plan_pid' => $plan->pid,
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $request = $user->toArray();
        $request['password'] = 'password2';
        $response = $this->basicRequest('POST', '/api/v0/users/', $request);
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($request, [
            'role_id',
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));
        $rep = json_decode($response->response->getContent());
        $this->seeInDatabase('user_setting', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->seeInDatabase('store_settings', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->seeInDatabase('subscription_user', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->seeAddressInDatabase($user->billing_address, 'Billing');
        $this->seeAddressInDatabase($user->shipping_address, 'Shipping');
    }

    public function testCreateRepFromCustomer()
    {
        $plan = factory(Plan::class)->create();
        $customer = factory(User::class)->create([
            'role_id' => 3,
        ]);
        $billing = factory(Address::class)->create([
            'label' => 'Billing',
            'addressable_id' => $customer->id,
        ]);
        $shipping = factory(Address::class)->create([
            'label' => 'Shipping',
            'addressable_id' => $customer->id,
        ]);
        $user = factory(User::class)->make([
            'email' => $customer->email,
            'role_id' => 5,
            'public_id' => uniqid(),
            'plan_pid' => $plan->pid,
            'password' => 'password2'
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $request = $user->toArray();
        $request['password'] = 'password2';
        $response = $this->basicRequest('POST', '/api/v0/users/', $request);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($request, [
            'role_id',
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));
        $rep = json_decode($response->response->getContent());
        $this->seeInDatabase('user_setting', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->seeInDatabase('store_settings', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->seeInDatabase('subscription_user', [
            'user_id' => $rep->id,
            'user_pid' => $rep->pid,
        ]);
        $this->notSeeInDatabase('addresses', $billing->toArray());
        $this->notSeeInDatabase('addresses', $shipping->toArray());
        $this->seeAddressInDatabase($user->billing_address, 'Billing');
        $this->seeAddressInDatabase($user->shipping_address, 'Shipping');
    }

    public function testCreateAdmin()
    {
        $plan = factory(Plan::class)->create();
        $user = factory(User::class)->make([
            'role_id' => 7,
            'public_id' => uniqid(),
            'plan_pid' => $plan->pid,
            'password' => 'password2'
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $request = $user->toArray();
        $request['password'] = 'password2';
        $response = $this->basicRequest('POST', '/api/v0/users/', $request);
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($request, [
            'role_id',
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));
        $admin = json_decode($response->response->getContent());
        $this->seeInDatabase('user_setting', [
            'user_id' => $admin->id,
            'user_pid' => $admin->pid,
        ]);
        $this->notSeeInDatabase('store_settings', [
            'user_id' => $admin->id,
            'user_pid' => $admin->pid,
        ]);
        $this->notSeeInDatabase('subscription_user', [
            'user_id' => $admin->id,
            'user_pid' => $admin->pid,
        ]);
    }

    public function testCreateSuperadmin()
    {
        $plan = factory(Plan::class)->create();
        $user = factory(User::class)->make([
            'role_id' => 8,
            'public_id' => uniqid(),
            'plan_pid' => $plan->pid,
            'password' => 'password2'
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $request = $user->toArray();
        $request['password'] = 'password2';
        $response = $this->basicRequest('POST', '/api/v0/users/', $request);
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($request, [
            'role_id',
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));
        $superadmin = json_decode($response->response->getContent());
        $this->seeInDatabase('user_setting', [
            'user_id' => $superadmin->id,
            'user_pid' => $superadmin->pid,
        ]);
        $this->notSeeInDatabase('store_settings', [
            'user_id' => $superadmin->id,
            'user_pid' => $superadmin->pid,
        ]);
        $this->seeAddressInDatabase($user->billing_address, 'Billing');
        $this->seeAddressInDatabase($user->shipping_address, 'Shipping');
        $this->notSeeInDatabase('subscription_user', [
            'user_id' => $superadmin->id,
            'user_pid' => $superadmin->pid,
        ]);
    }

    public function testUpdateRep()
    {
        $plan = factory(Plan::class)->create();
        $rep = factory(User::class)->create([
            'role_id' => 5,
        ]);
        $billing = factory(Address::class)->create([
            'label' => 'Billing',
            'addressable_id' => $rep->id,
        ]);
        $shipping = factory(Address::class)->create([
            'label' => 'Shipping',
            'addressable_id' => $rep->id,
        ]);
        $user = factory(User::class)->make([
            'public_id' => uniqid(),
            'password' => 'password2',
            'role_id' => 5,
        ]);
        $user->billing_address = factory(Address::class, 'request')->make();
        $user->shipping_address = factory(Address::class, 'request')->make();
        $request = $user->toArray();
        $request['password'] = 'password2';
        $response = $this->basicRequest('PATCH', '/api/v0/users/'.$rep->pid, $request);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'email' => $user->email
        ]);
        $this->seeInDatabase('users', array_only($request, [
            'public_id',
            'first_name',
            'last_name',
            'phone_number',
            'role_id',
            'join_date',
            'email',
        ]));
    }

    public function testGetCardToken()
    {
        $user = $this->createUser('Rep');
        $cardToken = [
            'token' => 'test',
            'user_id' => $user->id,
            'type' => 'subscription',
            'card_type' => 'Visa',
            'expiration' => '2023-12',
            'card_digits' => '************1111'
        ];
        DB::table('card_token')->insert($cardToken);
        $response = $this->basicRequest('GET', '/api/v0/users/'.$user->pid.'/card-token');
        $response->assertResponseStatus(200);
        $response->seeJson($cardToken);
    }

    private function seeAddressInDatabase($address, $label)
    {
        $this->seeInDatabase('addresses', [
            'address_1' => $address->line_1,
            'address_2' => $address->line_2,
            'city'      => $address->city,
            'state'     => $address->state,
            'zip'       => $address->zip,
            'label'     => $label,
        ]);
    }
}
