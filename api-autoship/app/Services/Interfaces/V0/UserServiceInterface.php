<?php

namespace App\Services\Interfaces\V0;

interface UserServiceInterface
{
    public function getBuyer($buyerPid, $subscription);
    public function getUser($buyerPid, $subscription);
}
