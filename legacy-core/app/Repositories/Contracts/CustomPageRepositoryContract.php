<?php

namespace App\Repositories\Contracts;

use App\Models\Doc;

interface CustomPageRepositoryContract
{
    public function createUpdate(array $inputs = []);
}
