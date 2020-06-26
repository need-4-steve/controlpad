<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;

interface SubscriptionRepositoryContract
{
    public function create(array $inputs = []);
    public function update(Subscription $subscription, array $inputs = []);
}
