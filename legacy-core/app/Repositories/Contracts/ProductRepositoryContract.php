<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\User;

interface ProductRepositoryContract
{
    public function index(array $request);
    public function create(array $inputs = []);
    public function getByInventoryAndCategory(int $user_id, $queryStrs);
}
