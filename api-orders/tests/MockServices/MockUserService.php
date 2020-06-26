<?php

namespace Test\MockServices;

use App\Services\UserServiceInterface;

class MockUserService implements UserServiceInterface
{
    public function getBusinessAddressForUser($userPid)
    {
        return (object)[
            'name' => 'Fake Name',
            'line_1' => '123 Main St',
            'line_2' => 'Apt 1',
            'city' => 'Orem',
            'state' => 'UT',
            'zip' => '84604'
        ];
    }

    public function getUserById($id, $addresses = false)
    {
        return (object) [
            'id' => $id,
            'full_name' => 'First Last',
            'email' => 'buyer@example.com',
            'role' => 'Rep'
        ];
    }

    public function getUserbyPid($pid, $addresses = false)
    {
        return (object) [
            'id' => 106,
            'pid' => $pid,
            'full_name' => 'First Last',
            'email' => 'buyer@example.com',
            'role' => 'Rep'
        ];
    }

    public function findUserByEmail($email, $addresses = false)
    {
        return (object) [
            'id' => 106,
            'email' => $email,
            'full_name' => 'First Last',
            'role' => 'Rep'
        ];
    }

    public function createCustomer($userId, $customer)
    {
        $customer['id'] = 106;
        $customer['role'] = 'Customer';
        return (object) $customer;
    }

    public function attachCustomer($userId, $customerPid)
    {
        return (object) [
            'id' => $customerPid,
            'full_name' => 'First Last',
            'email' => 'buyer@example.com',
            'role' => 'Rep'
        ];
    }
}
