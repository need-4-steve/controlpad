<?php

namespace App\Repositories\Contracts;

use App\Models\Bundle;

interface BundleRepositoryContract
{
    public function create(array $inputs = []);
    public function update(Bundle $bundle, array $inputs = []);
}
