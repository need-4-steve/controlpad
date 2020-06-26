<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryContract
{
    public function create(array $inputs = []);
    public function update(Payment $payment, array $inputs = []);
}
