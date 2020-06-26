<?php

namespace App\Repositories\Contracts;

use App\Models\Coupon;

interface CouponRepositoryContract
{
    public function create(array $inputs = []);
}
