<?php

use App\User;
use App\Address;
use Guzzle\Http\Client;
use Laravel\Lumen\Testing\DatabaseTransactions;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use DatabaseTransactions;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/testapp.php';
    }

    public function basicRequest($verb, $endpoint, $params = [])
    {
        return $this->json($verb, $endpoint, $params, ['APIKey' => 'Superadmin']);
    }

    public function createUser($role = 'Customer')
    {
        switch (strtolower($role)) {
            case 'rep':
                $roleId = 5;
                break;
            case 'admin':
                $roleId = 7;
                break;
            case 'superadmin':
                $roleId = 8;
                break;
            case 'customer':
                $roleId = 3;
                break;
            default:
                throw new \Exception('incorrect role chosen');
        }
        $user = factory(User::class)->create(['role_id' => $roleId]);
        $user->billing_address = factory(Address::class)->create([
            'addressable_id' => $user->id,
            'label' => 'Billing'
        ]);
        $user->shipping_address = factory(Address::class)->create([
            'addressable_id' => $user->id,
            'label' => 'Shipping'
        ]);
        if ($role === 'Rep') {
            $user->business_address = factory(Address::class)->create([
                'addressable_id' => $user->id,
                'label' => 'Business'
            ]);
        }
        return $user;
    }
}
