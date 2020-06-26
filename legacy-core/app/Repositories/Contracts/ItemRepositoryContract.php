<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use App\Models\Product;

interface ItemRepositoryContract
{
    public function create($productId, array $inputs = []);
}
