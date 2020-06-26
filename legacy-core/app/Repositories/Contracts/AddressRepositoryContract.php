<?php

namespace App\Repositories\Contracts;

use App\Models\Address;

interface AddressRepositoryContract
{
    public function create(array $inputs = []);
    public function show($id);
}
