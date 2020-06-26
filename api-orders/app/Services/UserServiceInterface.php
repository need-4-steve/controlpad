<?php

namespace App\Services;

interface UserServiceInterface
{
    public function getBusinessAddressForUser($userPid);
    public function getUserById($id, $addresses = false);
    public function getUserbyPid($pid, $addresses = false);
    public function findUserByEmail($email, $addresses = false);
    public function createCustomer($userId, $customer);
    public function attachCustomer($userId, $customerId);
}
